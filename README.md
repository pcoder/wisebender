wisebender
==========

An online Wiselib-based source compilation infrastructure for the cloud. This is a fork of [codebender.cc](www.codebender.cc). The (Wiselib)[www.wiselib.org] framework is difficult and time-consuming in installation, given the fact that we have so many different platforms. This project aims at easing this by providing user with cloud infrastructure to compile Wiselib-based apps.


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
	chmod 766 Symfony/app/cache

	php Symfony/app/check.php

Check that you have all the necessary stuff and add if any highly recommended PHP modules are missing.

Make sure that the logs folder is created and is writable.
	mkdir -p Symfony/app/logs
	chmod 766 Symfony/app/logs/

Create `parameters.ini` in `Symfony/app/config` folder. A sample `parameter.ini.dist` file is provided. Make sure to change the parameters according to your requirement.

Create a database that is needed by the application and set the appropriate database parameters in the parameters.ini file.

	create database wisebender;

Create the database schema for the application using doctrine.
	php app/console doctrine:schema:update --force

If everything goes fine, you should find the application up and running.


