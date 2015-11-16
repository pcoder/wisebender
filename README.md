Wisebender
==========

[![ScreenShot](https://github.com/pcoder/wisebender/blob/master/Wisebender-start.png?raw=true)](http://youtu.be/IOZQO1rmLt0)

[Wisebender](http://wisebender.cti.gr) is a Wiselib-based online source code editor infrastructure for the cloud. This is a fork of [codebender.cc](http://codebender.cc). The [Wiselib](http://www.wiselib.org) framework is difficult and time-consuming in installation. We have so many different platforms (including different WSN hardware platforms, Android, Linux, iOS, and the Shawn simulator), and installation/configuration for each platform is (i) _different_, (ii) _complicated_, and (iii) _time consuming_. Many developers prefer using preinstalled [Wiselib-based virtual machine](http://www.ibr.cs.tu-bs.de/alg/wisebed/). This may not be the quickest and the easiest way of developing applications based on Wiselib. Hence, this project aims at easing the aforementioned problems by providing user with cloud infrastructure to write/import/compile Wiselib-based source code on the fly.

This repository was created to manage the source code for the GSoC 2013 - [Wiselib Online Editing Service Project](https://google-melange.appspot.com/gsoc/project/google/gsoc2013/m_ravi/6001). More information on the status of the project and my experiences on the work can be found at the [Wisebender blog](http://wisebender.wordpress.com).

The application is under beta-testing [here](http://wisebender.cti.gr).

Functionalities
---------------

The Wisebender facilitates a Wiselib developer by providing the following functionalities.

1. Allows user to clone Wiselib Framework source and edit it instantly on the browser and also allows user to save the source code on the cloud. The a user can have multiple clones of Wiselib Framework.

2. Allows the user to create Wiselib-based applications (projects) on the cloud, edit the source code and compile it against one of the chosen Wiselib Frameworks for a particular OS. Currently, iSense 5139, iSense 5148, and Shawn simulator are supported. The output of the compilation is a binary file which can be downloaded and flashed/used on a corresponding device.

3. Allows user to instantly save a project as a repository on GitHub and vice versa. A user can choose to delete it from GitHub also. 
 
4. Allows user to Fork a Wiselib-based application code on GitHub and import it into Wisebender as a project.


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

Notes
-----

1. The project uses [OAuth.io](https://oauth.io/) service for obtaining the `access_token` for GitHub requests. To make this working, one needs to register the service and create an application for GitHub allowing the following scopes: `delete_repo`, `public_repo`, `repo`, `repo:status`, `user`, `user:email`. Once this is done, the public key generated for the application must be passed to `OAuth.initialize();` method on line 56 of `Symfony/src/Ace/GenericBundle/Resources/views/Editor/editor_javascript.html.twig` file.
2. The project uses simple PHP scripts for obtaining codes for compiling against various platforms and for download of the binary file (See project [wb-compiler](https://github.com/pcoder/wb-compiler)). The files `Symfony/web/compiler.php` and `Symfony/web/download.php` have some hardcoded paths for locating Project's sketches and compilation output directory. They must be appropriately updated for compilation and download binary to work properly.
3. In order to compile a Wiselib-based application, the compiler must be supplied with a class with a main function. Wisebender assumes this class as `{project_name}_app.cpp` file, which is created by default when a project is created. In case of deletion of this file or using a new file as a main class, the compilation may not work properly. So, it is recommended that the main class (the class with the main method) follows this naming convention.


[![Bitdeli Badge](https://d2weczhvl823v0.cloudfront.net/pcoder/wisebender/trend.png)](https://bitdeli.com/free "Bitdeli Badge")

