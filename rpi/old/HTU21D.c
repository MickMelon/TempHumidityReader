#include <unistd.h>
#include <stdio.h>
#include <stdlib.h>
#include <math.h>

#include "wiringPi.h"
#include "wiringPiI2C.h"
#include "HTU21D.h"


#define PATH_MAX 100


double getSystemTemperature() {
	FILE *fp;
	char path[PATH_MAX];
	char *ptr;
	double systemTemp = 0.0;

	fp = popen("vcgencmd measure_temp | egrep -o '[0-9]*\\.[0-9]*'", "r");
	if (fp == NULL) {
		printf("Could not read system temperature\n");
		return 0;
	}

	while (fgets(path, PATH_MAX, fp) != NULL) {
		systemTemp = strtod(path, &ptr);
	}

	return systemTemp;
}

u_int16_t readValue(int fd, int cmd) {
	unsigned char buf [4];

	wiringPiI2CWrite(fd, cmd);

	delay(50);

	read(fd, buf, 3);

	unsigned int value = (buf [0] << 8 | buf [1]) & 0xFFFC;

	return value;
}

/**
 * Calculate the dew point and return in degrees C.
 */ 
float computeDewPoint(float temperature, float relative_humidity) {
	double partial_pressure;
	double dew_point;

	partial_pressure = pow(10, HTU21D_CONSTANT_A - HTU21D_CONSTANT_B / (temperature + HTU21D_CONSTANT_C));
	dew_point = - HTU21D_CONSTANT_B / (log10(relative_humidity * partial_pressure / 100) - HTU21D_CONSTANT_A) - HTU21D_CONSTANT_C;

	return (float)dew_point;
}

/**
 * Returns the compensated humidity in %RH
 */ 
float computeCompensatedHumidity(float temperature, float relative_humidity) {
	return (relative_humidity + (25 - temperature) * HTU21D_TEMPERATURE_COEFFICIENT);
}

// Get humidity
float getRelativeHumidity(int fd)
{
  	unsigned int humid = readValue(fd, HTU21D_HUMID);
	float rh = -6 + 125 * (humid / pow(2, 16));
	return rh;
	
	// Convert sensor reading into humidity.
	// See page 15 of the datasheet
//	double tSensorHumid = humid / 65536.0;
//	return -6.0 + (125.0 * tSensorHumid);

//	float humidity = (float)humid * HUMIDITY_COEFF_MUL / (1UL<<16) + HUMIDITY_COEFF_ADD;
//	return humidity;
}


// Get temperature
float getTemperature(int fd)
{
	u_int16_t temp = readValue(fd, HTU21D_TEMP);
	
	// Convert sensor reading into temperature.
	// See page 14 of the datasheet
	//float tSensorTemp = temp / 65536.0;
	//return -46.85 + (175.72 * tSensorTemp);

	//float temperature = (float)temp * TEMPERATURE_COEFF_MUL / (1UL<<16) + TEMPERATURE_COEFF_ADD;	
	//return temperature;

	float tmp = -46.85 + 175.72 * (temp / pow(2, 16));
	return tmp;
}

struct Reading getReading(int fd) {
	float temperature = getTemperature(fd);
	float humidity = getRelativeHumidity(fd);

	float dewPoint = computeDewPoint(temperature, humidity);
	float compensatedHumidity = computeCompensatedHumidity(temperature, humidity);

	printf("temp:%f|hum:%f|dew:%f|comp:%f", temperature, humidity, dewPoint, compensatedHumidity);

	struct Reading r = {.temperature = temperature, .humidity = compensatedHumidity};
	return r;
}