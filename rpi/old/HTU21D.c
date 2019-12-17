#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <math.h>

#include "wiringPi.h"
#include "wiringPiI2C.h"
#include "HTU21D.h"

/**
 * Issue a write command to read a value from HTU21D. 
 * Commands are listed in the header file.
 * 
 * @param int fd The HTU21D file.
 * @param int cmd The command (see HTU21D.h)
 * 
 * @return u_int16_t The value read after executing the command
 */ 
u_int16_t readValue(int fd, int cmd) {
	unsigned char buf[4];

	wiringPiI2CWrite(fd, cmd);
	delay(100);
	read(fd, buf, 3);
	u_int16_t value = (buf [0] << 8 | buf [1]) & 0xFFFC;

	return value;
}

/**
 * Calculate the dew point and return in degrees C.
 * 
 * @param float temperature Reading from HTU21D
 * @param float relativeHumidity Reading from HTU21D
 * 
 * @return float Dew point
 */ 
float computeDewPoint(float temperature, float relativeHumidity) {
	float partialPressure;
	float dewPoint;

	partialPressure = pow(10, HTU21D_CONSTANT_A - HTU21D_CONSTANT_B / (temperature + HTU21D_CONSTANT_C));
	dewPoint = - HTU21D_CONSTANT_B / (log10(relativeHumidity * partialPressure / 100) - HTU21D_CONSTANT_A) - HTU21D_CONSTANT_C;

	return (float)dewPoint;
}

/**
 * Returns the compensated humidity in %RH.
 * 
 * @param float temperature Reading from HTU21D
 * @param float relativeHumidity Reading from HTU21D
 * 
 * @return float Compensated humidity.
 */ 
float computeCompensatedHumidity(float temperature, float relativeHumidity) {
	return (relativeHumidity + (25 - temperature) * HTU21D_TEMPERATURE_COEFFICIENT);
}

/**
 * Read the relative humidity level from HTU21D in %RH.
 * 
 * @param int HTU21D file.
 * 
 * @return float Relative humidity.
 */ 
float getRelativeHumidity(int fd)
{
  	u_int16_t humid = readValue(fd, HTU21D_HUMID);
	return -6 + (125 * ((double)humid / 65536));
}

/**
 * Read the temperature from HTU21D and carry out the conversions
 * to get the actual temperature in C.
 * 
 * @param int HTU21D file.
 * 
 * @return float Temperature.
 */ 
float getTemperature(int fd)
{
	u_int16_t temp = readValue(fd, HTU21D_TEMP);
	return -46.85 + 175.2 * ((double)temp / 65536);
}

/**
 * The interface function that gets all the readings from
 * HTU21D and returns it in a struct.
 * 
 * @int fd HTU21D file.
 * 
 * @return struct Reading
 */ 
struct Reading getHTU21DReading(int fd) {
	float temperature = getTemperature(fd);
	float humidity = getRelativeHumidity(fd);

	float dewPoint = computeDewPoint(temperature, humidity);
	float compensatedHumidity = computeCompensatedHumidity(temperature, humidity);

	printf("HTU21D: temp:%f|hum:%f|dew:%f|comp:%f\n", temperature, humidity, dewPoint, compensatedHumidity);

	struct Reading r = {.temperature = temperature, .humidity = humidity};
	return r;
}