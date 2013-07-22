Wisebender
==========

Wisebender is a proposed Wiselib-based online source code editor infrastructure for the cloud. This is a fork of [codebender.cc](http://codebender.cc). The [Wiselib](http://www.wiselib.org) framework is difficult and time-consuming in installation. We have so many different platforms (including different WSN hardware platforms, Android, Linux, iOS, and the Shawn simulator), and installation/configuration for each platform is (i) _different_, (ii) _complicated_, and (iii) _time consuming_. Many developers prefer using preinstalled [Wiselib-based virtual machine](http://www.ibr.cs.tu-bs.de/alg/wisebed/). This may not be the quickest and the easiest way of developing applications based on Wiselib. Hence, this project aims at easing the aforementioned problems by providing user with cloud infrastructure to write/import/compile Wiselib-based source code on the fly.

This repository was created to manage the source code for the GSoC 2013 - [Wiselib Online Editing Service Project](https://google-melange.appspot.com/gsoc/project/google/gsoc2013/m_ravi/6001). More information on the status of the project and my experiences on the work can be found at the [Wisebender blog](http://wisebender.wordpress.com).


Proposition
-----------

Allow Wiselib-based code to be compiled on the cloud in of the following three ways.

1. Fork Wiselib GitHub code and allow the user to modify the code on the platform

2. Upload the user to upload his/her own Wiselib-based code

3. Import a Wiselib-based code on GitHub repository into Wisebender and then compile it.


Installation
------------

	git clone git://github.com/pcoder/wisebender.git

	cd wisebender

	mkdir -p Symfony/app/cache
	chmod 777 Symfony/app/cache
	mkdir -p Symfony/app/logs
	chmod 777 Symfony/app/logs
	
	php Symfony/app/check.php

Check that you have all the necessary stuff and add if any highly recommended PHP modules are missing.

Create `parameters.ini` in `Symfony/app/config` folder. A sample `parameter.ini.dist` file is provided. Make sure to change the parameters according to your requirement. If you are using hard disk drive for storage of your files (sketches); make sure you have `storagelayer` and `disk.directory` parameters defined 
	
	storagelayer="disk"
	disk.directory="/home/wiselib/wisebender/Symfony/data"

If you are using MySQL, the pdo driver may not have been installed by default. You can install it using:

	sudo apt-get install php5-mysql

And then restart the apache server.
	
	sudo /etc/init.d/apache2 restart

Create a database that is needed by the application and set the appropriate database parameters in the parameters.ini file.

	mysql> create database wisebender;

Create the database schema for the application using doctrine.

	php app/console doctrine:schema:update --force

If everything goes fine, you should find the application up and running.

