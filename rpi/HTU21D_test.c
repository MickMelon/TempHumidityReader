#include <stdio.h>
#include <errno.h>
#include <stdlib.h>
#include <string.h>
#include "wiringPi.h"
#include "wiringPiI2C.h"
#include <curl/curl.h>

#include "HTU21D.h"

int sendToServer(double temperature, double humidity) {
	CURL *curl;
	CURLcode res;

	curl_global_init(CURL_GLOBAL_ALL);
	curl = curl_easy_init();
	if (curl == NULL) {
		return 128;
	}

	char jsonObj[100];
	sprintf(jsonObj, "{ \"temperature\": \"%lf\", \"humidity\": \"%lf\" }", temperature, humidity);

	struct curl_slist *headers = NULL;
	headers = curl_slist_append(headers, "Accept: application/json");
	headers = curl_slist_append(headers, "Content-Type: application/json");
	headers = curl_slist_append(headers, "charsets: utf-8");

	curl_easy_setopt(curl, CURLOPT_URL, "http://192.168.1.6/~michael/miniproject/web/index.php?c=api&a=create");

	curl_easy_setopt(curl, CURLOPT_CUSTOMREQUEST, "POST");
	curl_easy_setopt(curl, CURLOPT_HTTPHEADER, headers);
	curl_easy_setopt(curl, CURLOPT_POSTFIELDS, jsonObj);
	curl_easy_setopt(curl, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)");

	res = curl_easy_perform(curl);

	curl_easy_cleanup(curl);
	curl_global_cleanup();
	return res;
}

int main ()
{
	wiringPiSetup();
	int fd = wiringPiI2CSetup(HTU21D_I2C_ADDR);
	if ( 0 > fd )
	{
		fprintf (stderr, "Unable to open I2C device: %s\n", strerror (errno));
		exit (-1);
	}

	double temperature = getTemperature(fd);
	double humidity = getHumidity(fd);
	
	printf("%5.2fC\n", temperature);
	printf("%5.2f%%rh\n", humidity);

	printf("Sending to server...\n");

	int res = sendToServer(temperature, humidity);
	
	printf("Sent to server. got result code: %i \n", res);

	return 0;
}