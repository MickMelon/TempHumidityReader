# Temperature and Humidity Reading with HTML Interface

Companies who run datacentres must ensure that the environment in which their hardware is located is safe. If the server room were to get too hot or humid, the hardware could corrode and subsequently fail. In contrast, if the server room were to get too cold or have low humidity, the hardware becomes prone to electrostatic discharge (Grundy, 2005). If this were to occur, then it would have seriously negative impacts for the company including loss of money and potential loss of data that is critical to the company’s operations. It is then vital that the companies implement a monitoring system to ensure the temperature and humidity stay within the safe limitations. It is not feasible that the employees enter the server rooms to manually check the room conditions, therefore Internet of Things (IoT) technologies would be a very effective implementation for such a problem. Humidity and temperature sensors can be placed in the room and connected to a cheap computer so the readings can be sent across a network to a server, stored in a database, and subsequently displayed on a web page. 

The aim of this project is to build a temperature and humidity reading system that can be accessible on the cloud via a web page. This system must ensure that the best recommended security methods are implemented. The following objectives have been identified and are required in order to complete the project: 

- Build the hardware
- Setup the cloud server
- Develop an API to store and retrieve readings
- Create a web page to display results
- Implement security measures


## Methodology

This project is split into three sections: hardware, cloud, and web application. The hardware will consist of setting up a Raspberry Pi with a HTU21D humidity sensor and writing the code to read the sensor. The humidity and temperature readings will be sent to the web application API on a cloud server which will in turn store the readings in a database. The readings will then be displayed to the user on a web page. Each section will have its own security requirements as they will involve working with different technologies. 

### Hardware

The pieces of hardware that have been used for this project are: 
1.	Raspberry Pi 4 with Raspbian 
2.	HTU21D humidity sensor 
3.	Mini breadboard 
4.	4 male-to-female jumper wires 


A tutorial written by leon-anavi was used as a reference in setting up the hardware (leon-anavi, n.d.) along with the HTU21D datasheet (Measurement Specialties, n.d.). Figure 2 shows how the HTU21D humidity sensor was connected to the Raspberry Pi while figure 3 shows the physical connections. The Raspberry Pi was encased to prevent physical damage to the hardware.  

| **HTU21D** | **Pin Name** | **Pin No** |
| --- | --- | --- |
| VIN (voltage in) | 3V3 | 1 |
| GND (ground) | GND | 9 |
| SDA (data line) | GPIO3 | 5 |
| SCL (clock line) | GPIO2 | 3 |

The build-essential and i2c-tools packages were installed on the Raspberry Pi and the I2C module was enabled via raspi-config to enable use of the I2C interface to communicate with HTU21D (Robot Electronics, n.d.). 

Open source code was used to test the sensor (leon-anavi, 2017). The values read from the sensor needs to be converted into relative humidity (RH) and Celsius. This formula can be found on page 15 of the HTU21D datasheet. See figure 4 and 5 for the code used to read the value from the sensor and convert it to relative humidity.  

Values were being read successfully from the sensor and displayed in the console, however they were highly inaccurate; the temperature reading was around -70C while the humidity reading was around -50%RH. An open source HTU21D driver (TEConnectivity, 2018) was used in attempt to improve the accuracy. This time, results were non-negative but still seemed to be incorrect. The output can be seen in figure 7. The final implementation took inspiration from both repositories. Additionally, the temperature of the Raspberry Pi itself was read by executing a shell command and retrieving the results. 

The readings were encrypted inside a JSON Web Token (JWT) using the libjwt C library (benmcollins, 2019) and sent as an HTTP request using the libcurl C library (haxx.se, n.d.) to the API on the cloud server. This JWT allows the server to authenticate the Raspberry Pi and ensures that the data being sent across the network is totally encrypted. This has been setup to send to the server every 5 seconds by using C’s sleep function. 

### Cloud

An Amazon Web Services (AWS) EC2 instance was launched running the Ubuntu 18.04 Linux distribution. The security groups were set to only allow HTTP, HTTPS, and SSH on ports 80, 443, and 22, respectively. SSH access was also restricted to a specific IP address. Ubuntu was updated to the latest version to prevent security issues caused by outdated software. The LAMP (Linux Apache MySQL PHP) stack was chosen for running the webserver. LAMP was setup by installing the appropriate packages on Ubuntu (Drake, 2018), along with the phpMyAdmin package for simple database management.  

It is essential that SSL certificates are used when running any website because all communications between the client and webserver should be encrypted. Apache was configured to use a self-signed certificate, redirect HTTP requests to HTTPS, and to enable the SSL and headers modules (Ellingwood, 2016). 

The default installation of Apache is not secure enough to be used in a production environment because it exposes information about itself like its version and allows a user to view the files in a directory (Ostryzhko, 2018). This information is useful to an attacker and therefore was protected by disabling directory listing and version display in the config files to hinder their reconnaissance efforts. 

The ModSecurity and ModEvasive Apache modules were installed for intrusion detection and protection of a variety of attacks including brute force and DDoS. Trace HTTP request was disabled to disallow cross site tracing to make it difficult for an attacker to steal cookie information (LinuxHostSupport, 2018). 

### Web Application

An API was developed in PHP following the Model-View-Controller (MVC) design pattern. Three API methods were created: get, latest, and create. Get and latest are available for all users but create, as seen in figure 8, will only work from a whitelisted IP address and valid JWT. The php-jwt library was used for decoding the tokens (Firebase, 2019). This prevents unauthorised users from creating new database records.  The API receives input from the user, which can never be trusted, therefore all input from external sources was sanitised. PDO was used to allow for prepared MySQL statements which prevents SQL injection attacks. A simple HTML interface  was created to display the latest sensor readings and a visual representation using Chart.js and updated periodically using AJAX 

## Conclusion

This project aimed to develop a temperature and humidity monitoring system that encompasses IoT technologies by sending the sensor data to the cloud and then displaying the results on a web page for the user to see, with a requirement of appropriately securing each aspect. The implementation of this project was successful to some extent.  

The temperature and humidity readings can be read from the sensor; however, the readings appear to be inaccurate despite trying different conversion methods to get the values in Celsius and relative humidity. It is speculated that this is because the humidity sensor requires calibration. If this project were to be expanded upon, this would be the first area to conduct further investigation. Another HTU21D humidity sensor should be used to confirm whether the problem is the conversion or the hardware. 

The readings from the sensor can be successfully sent to an Amazon EC2 cloud server instance and then stored in a MySQL database. The cloud server has been secured by restricting ports and only allowing SSH remote access with a key pair and from a specific IP. The webserver software has been secured by following the recommended practices including SSL certificates, installing Apache security modules, and preventing reconnaissance by hiding details about the server from the user. 

The API does its job in storing and retrieving sensor readings, with effective security using JWT and a whitelist to prevent unauthorised users from creating records. The system is 
protected from dangerous user input by sanitising request parameters and using prepared statements to prevent SQL injection.  

## References

benmcollins, 2019. _benmcollins/libjwt: JWT C Library._ [Online] Available at: https://github.com/benmcollins/libjwt [Accessed 17 December 2019].

Drake, M., 2018. _How To Install Linux, Apache, MySQL, PHP (LAMP) stack on Ubuntu 18.04._ [Online]
Available at: https://www.digitalocean.com/community/tutorials/how-to-install-linux-apachemysql-php-lamp-stack-ubuntu-18-04 [Accessed 17 December 2019].

Ellingwood, J., 2016. _How To Create a Self-Signed SSL Certificate for Apache in Ubuntu 16.04._ [Online]
Available at: https://www.digitalocean.com/community/tutorials/how-to-create-a-self-signedssl-certificate-for-apache-in-ubuntu-16-04 [Accessed 17 December 2019].

Firebase, 2019. _firebase/php-jwt: PEAR package for JWT._ [Online]
Available at: https://github.com/firebase/php-jwt [Accessed 17 December 2019].

Grundy, R., 2005. _Recommended Data Center Temperature &amp; Humidity._ [Online] Available at: https://avtech.com/articles/3647/recommended-data-center-temperaturehumidity/
[Accessed 17 December 2019].

haxx.se, n.d.. _libcurl - programming tutorial._ [Online] Available at: https://curl.haxx.se/libcurl/c/libcurl-tutorial.html [Accessed 17 December 2019].

leon-anavi, 2017. _leon-anavi/rpi-examples: Raspberry Pi examples._ [Online] Available at: https://github.com/leon-anavi/rpi-examples [Accessed 17 December 2019].

leon-anavi, n.d.. _Detect Temperature and Humidity With Raspberry Pi and HTU21 / SHT21._
[Online]
Available at: https://www.instructables.com/id/Detect-Temperature-and-Humidity-With-Raspberry-Pi-/
[Accessed 17 December 2019].

LinuxHostSupport, 2018. _How to Install mod\_security and mod\_evasive on Ubuntu 16.04._
[Online]
Available at: https://linuxhostsupport.com/blog/how-to-install-mod\_security-andmod\_evasive-on-ubuntu-16-04/
[Accessed 17 December 2019].

Measurement Specialties, n.d.. _Digital Relative Humidity sensor with Temperature output._
[Online]
Available at: https://cdn-shop.adafruit.com/datasheets/1899\_HTU21D.pdf [Accessed 17 December 2019].

Ostryzhko, M., 2018. _How to Harden Your Apache Web Server on an Ubuntu 18.04 Dedicated Server or VPS._ [Online]
Available at: https://hostadvice.com/how-to/how-to-harden-your-apache-web-server-onubuntu-18-04/
[Accessed 17 December 2019].

Robot Electronics, n.d.. _I2C tutorial._ [Online]
Available at: https://www.robot-electronics.co.uk/i2c-tutorial [Accessed 17 December 2019].

TEConnectivity, 2018. _TEConnectivity/HTU21D\_Generic\_C\_Driver: Generic C driver for the HTU2xD(F) sensor._ [Online]
Available at: https://github.com/TEConnectivity/HTU21D\_Generic\_C\_Driver [Accessed 17 December 2019].