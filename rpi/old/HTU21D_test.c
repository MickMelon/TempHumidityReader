#include <stdio.h>
#include <errno.h>
#include <stdlib.h>
#include <string.h>
#include "wiringPi.h"
#include "wiringPiI2C.h"
#include <curl/curl.h>
#include <unistd.h>

#include "HTU21D.h"

#define API_ADDRESS "https://35.174.12.122/api/create"


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

	char jsonObj[100];
	sprintf(jsonObj, "{ \"temperature\": \"%lf\", \"humidity\": \"%lf\", \"system_temp\": \"%lf\" }",
		temperature, humidity, systemTemp);

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
