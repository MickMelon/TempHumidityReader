#ifndef _HTU21D_H_
#define _HTU21D_H_

#define HTU21D_I2C_ADDR                                     0x40
#define HTU21D_TEMP                                         0xF3
#define HTU21D_HUMID                                        0xF5

#define HTU21D_TEMPERATURE_COEFFICIENT						(float)(-0.15)
#define HTU21D_CONSTANT_A									(float)(8.1332)
#define HTU21D_CONSTANT_B									(float)(1762.39)
#define HTU21D_CONSTANT_C									(float)(235.66)

// Coefficients for temperature computation
#define TEMPERATURE_COEFF_MUL								(175.72)
#define TEMPERATURE_COEFF_ADD								(-46.85)

// Coefficients for relative humidity computation
#define HUMIDITY_COEFF_MUL									(125)
#define HUMIDITY_COEFF_ADD									(-6)

struct Reading {
	float temperature;
	float humidity;
};

struct Reading getHTU21DReading(int fd);

#endif
