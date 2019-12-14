#include <stdio.h>
#include <errno.h>
#include <stdlib.h>
#include <string.h>
#include <jwt.h>
#include "wiringPi.h"
#include "wiringPiI2C.h"
#include <curl/curl.h>
#include <unistd.h>

#include "HTU21D.h"

#define API_ADDRESS "https://54.163.93.215/api/create"
#define JWT_KEY "duAM1RgJQO77LY7AkR8dMQO2JdURQ6ZU"
#define SLEEP_SECONDS 3

/**
 * Creates an encoded JSON web token that contains the HTU21D
 * readings. This is required by the PHP on the server.
 * 
 * @param float temperature The HTU21D temperature reading.
 * @param float humidity The HTU21D humidity reading.
 * @param float piTemp The Raspberry Pi temperature.
 * 
 * @return char* The encoded JWT.
 */ 
char* createEncodedJWT(float temperature, float humidity, float piTemp) {
	unsigned char key[32] = JWT_KEY;
	jwt_t *jwt = NULL;
	int ret = 0;
	char *encoded;
	char strTemperature[50];
	char strHumidity[50];
	char strPiTemp[50];

	// Convert the temperature and humidity into strings so 
	// they can be used with the jwt_add_grant() function.
	sprintf(strTemperature, "%e", temperature);
	sprintf(strHumidity, "%e", humidity);
	sprintf(strPiTemp, "%e", piTemp);

	printf("Creating new JWT key\n");

	// Create new JWT object
	ret = jwt_new(&jwt);
	if (ret) {
		printf("Error creating new JWT (return: %i)\n", ret);
		return NULL;
	}

	printf("JWT created\n");

	// Add temperature
	ret = jwt_add_grant(jwt, "temperature", strTemperature);
	if (ret) {
		printf("Error adding temperature grant: %d\n", ret);
		return NULL;
	}

	// Add humidity
	ret = jwt_add_grant(jwt, "humidity", strHumidity);
	if (ret) {
		printf("Error adding humidity grant: %d\n", ret);
		return NULL;
	}

	// Add pi temp
	ret = jwt_add_grant(jwt, "piTemp", piTemp);
	if (ret) {
		printf("Error adding piTemp grant: %d\n", ret);
		return NULL;
	}

	printf("JWT grants added\n");

	// Set algorithm
	ret = jwt_set_alg(jwt, JWT_ALG_HS256, key, sizeof(key));
	if (ret) {
		printf("Error setting JWT algorithm: %d\n", ret);
		return NULL;
	}

	printf("JWT algorithm set\n");

	// Encode JWT object into a string
	encoded = jwt_encode_str(jwt);
	if (!encoded) {
		printf("Error encoding JWT: %d\n", ret);
		return NULL;
	}

	printf("JWT string encoded\n");

	jwt_free(jwt);

	return encoded;
}

/**
 * Gets the temperature of the Raspberry Pi by issuing
 * a shell command and reading the output.
 * 
 * @return float The system temperature
 */ 
float getSystemTemperature() {
	FILE *fp;
	char path[100];
	char *ptr;
	double systemTemp;

	// Run the measure temp shell command
	fp = popen("vcgencmd measure_temp | egrep -o '[0-9]*\\.[0-9]*'", "r");
	if (fp == NULL) {
		printf("Could not read system temperature\n");
		return 0;
	}

	// Get the output
	while (fgets(path, 100, fp) != NULL) {
		systemTemp = strtod(path, &ptr);
	}

	pclose(fp);

	return (float)systemTemp;
}

/**
 * Makes a HTTP POST request to the AWS server with the sensor readings.
 * 
 * @param char* jsonObj The JSON object to be sent to the server.
 * 
 * @return int Result code.
 */ 
int sendToServer(char* jsonObj) {
	CURL *curl;
	CURLcode res;

	curl_global_init(CURL_GLOBAL_ALL);
	curl = curl_easy_init();
	if (curl == NULL) {
		return 128;
	}

	struct curl_slist *headers = NULL;
	headers = curl_slist_append(headers, "Accept: application/json");
	headers = curl_slist_append(headers, "Content-Type: application/json");
	headers = curl_slist_append(headers, "charsets: utf-8");

	curl_easy_setopt(curl, CURLOPT_URL, API_ADDRESS);
	curl_easy_setopt(curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);
	curl_easy_setopt(curl, CURLOPT_POSTFIELDS, jsonObj);
	curl_easy_setopt(curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)");

	// To prevent issue with self signed certificate
	curl_easy_setopt(curl, CURLOPT_SSL_VERIFYHOST, 0);
	curl_easy_setopt(curl, CURLOPT_SSL_VERIFYPEER, 0);

	res = curl_easy_perform(curl);

	curl_easy_cleanup(curl);
	curl_global_cleanup();

	return res;
}

/**
 * The entry point to the program. Inits WiringPi and sends 
 * the HTU21D reading to the server periodically.
 * 
 * @return int
 */ 
int main() {
	int fd, res;
	struct Reading reading;
	float piTemp;

	wiringPiSetup();

	fd = wiringPiI2CSetup(HTU21D_I2C_ADDR);
	if (fd < 0) {
		fprintf(stderr, "Unable to open I2C device: %s\n", strerror(errno));
		exit(-1);
	}
	
	while (1) {
		reading = getHTU21DReading(fd);
		piTemp = getSystemTemperature();

		char* jwt = createEncodedJWT(reading.temperature, reading.humidity, piTemp);
		if (jwt == NULL) {
			printf("Error creating the JWT!\n");
			return -1;
		}

		printf("JWT = %s\n", jwt);

		char jsonObj[512];
		sprintf(jsonObj, "{ \"jwt\": \"%s\" }", jwt);

		res = sendToServer(jsonObj);
		printf("Sent to server. Result code: %d\n", res);

		sleep(SLEEP_SECONDS);
	}

	return 0;
}
