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

char* createKey() {
	unsigned char key[32] = "TestKeyMate";
	jwt_t *jwt = NULL;
	int ret = 0;
	char *encoded;

	printf("Creating new JWT key\n");

	ret = jwt_new(&jwt);
	if (ret) {
		printf("Error creating new JWT (return: %i)\n", ret);
		return NULL;
	}

	printf("JWT created\n");

	ret = jwt_add_grant(jwt, "iss", "http://iss.com");
	if (ret) {
		printf("Error adding iss grant: %d\n", ret);
		return NULL;
	}

	ret = jwt_add_grant(jwt, "aud", "http://aud.com");
	if (ret) {
		printf("Error adding aud grant: %d\n", ret);
		return NULL;
	}

	ret = jwt_add_grant(jwt, "iat", "1356999524");
	if (ret) {
		printf("Error adding iat grant: %d\n", ret);
		return NULL;
	}

	ret = jwt_add_grant(jwt, "nbv", "1357000000");
	if (ret) {
		printf("Error adding nbv grant: %d\n", ret);
		return NULL;
	}

	ret = jwt_add_grant(jwt, "temperature", "123.123");
	if (ret) {
		printf("Error adding temperature grant: %d\n", ret);
		return NULL;
	}

	ret = jwt_add_grant(jwt, "humidity", "50.50");
	if (ret) {
		printf("Error adding humidity grant: %d\n", ret);
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

	encoded = jwt_encode_str(jwt);
	if (!encoded) {
		printf("Error encoding JWT: %d\n", ret);
		return NULL;
	}

	printf("JWT string encoded\n");

	jwt_free(jwt);

	return encoded;
}

float getSystemTemperature() {
	FILE *fp;
	char path[100];
	char *ptr;
	double systemTemp;

	fp = popen("vcgencmd measure_temp | egrep -o '[0-9]*\\.[0-9]*'", "r");
	if (fp == NULL) {
		printf("Could not read system temperature\n");
		return 0;
	}

	while (fgets(path, 100, fp) != NULL) {
		systemTemp = strtod(path, &ptr);
	}

	pclose(fp);

	return (float)systemTemp;
}

/**
 * Makes a HTTP POST request to the AWS server with the sensor readings.
 */ 
int sendToServer(float temperature, float humidity, float systemTemp) {
	CURL *curl;
	CURLcode res;

	curl_global_init(CURL_GLOBAL_ALL);
	curl = curl_easy_init();
	if (curl == NULL) {
		return 128;
	}

	char* key = createKey();
	if (key == NULL) {
		printf("Error creating the JWT key!\n");
		return -1;
	}

	
	//sprintf(jsonObj, "{ \"temperature\": \"%lf\", \"humidity\": \"%lf\", \"system_temp\": \"%lf\" }",
	//	temperature, humidity, systemTemp);
	char jsonObj[256];
	printf("Key is: %s\n", key);

	sprintf(jsonObj, "{ \"jwt\": \"%s\" }", key);

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

void sendPeriodically() {
	int fd = wiringPiI2CSetup(HTU21D_I2C_ADDR);
	if ( 0 > fd )
	{
		fprintf (stderr, "Unable to open I2C device: %s\n", strerror (errno));
		exit (-1);
	}
	
//	double temperature = getTemperature(fd);
//	//double humidity = getHumidity(fd);
//	double systemTemp = getSystemTemperature();

	struct Reading r = getReading(fd);
	
	printf("\nSensor temp: %5.2fC\n", r.temperature);
	printf("Humidity: %5.2f%%rh\n", r.humidity);
	//printf("System temp: %5.2fC\n", systemTemp);

	printf("Sending to server...\n");

	int res = sendToServer(r.temperature, r.humidity, 0);
	
	printf("Sent to server. got result code: %i \n", res);
}

int main() {
	wiringPiSetup();
		
	while (1) {
		sendPeriodically();
		sleep(1);
	}

	return 0;
}
