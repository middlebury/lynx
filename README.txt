Name: Lynx Personal/Social Bookmarking
Author: Adam Franco 
URI: http://github.com/adamfranco/lynx
Copyright (c) 2009, The President and Fellows of Middlebury College.
License: GNU General Public License (GPL) version 3 or later. (See LICENSE.txt for details.)



What is Lynx?
------------------
Lynx is an open-source clone of a popular social-bookmarking tool designed to be
integrated into a number of web-applications hosted by a university or similar 
organization.

Lynx makes use of the Central Authentication Service (CAS) maintained by the Jasig
project (http://www.jasig.org/cas) and commonly used in university environments for
web-single-sign-on. Lynx relies on CAS's support of proxy-authentication to enable
users to interact with Lynx both at the Lynx application itself as well as through 
interfaces embedded in other web-application.


Requirements
-------------------
* A web host with PHP 5.2 or later and the following modules:
	- pdo
	- pdo-mysql
	- curl
	
* A MySQL database version 5.0 or later.
  The database user creating the tables and procedures will need grants to allow
  the creation of triggers and stored procedures. If not creating the schema as
  a SUPER user, the CREATE ROUTINE and ALTER ROUTINE grants will be needed.
  See the following for more information:
  http://dev.mysql.com/doc/refman/5.1/en/stored-routines-privileges.html

* An operational Central Authentication Service (CAS)


Installation
-------------------
 * Clone the Lynx repository to a NON-web-accessbile directory:
 	
 	cd /var/www/
 	git clone git://github.com/adamfranco/lynx.git
 	cd lynx
 	git submodule init
 	git submodule update
 	
 * Update the CAS server and database configuration lines in lynx/application/configs/application.ini
 
 * Install the database schema located at lynx/application/modules/lynx/models/*.sql
 	
 	mysql -h db.example.com -u username -p -D databasename < lynx/application/modules/lynx/models/tables.sql
 	mysql -h db.example.com -u username -p -D databasename < lynx/application/modules/lynx/models/procedures.sql
 
 * Make a symbolic link to the lynx/public/ directory in your web-root:
 	
 	cd /var/www/html/
 	ln -s /var/www/lynx/public/ lynx
 
 * Set the APPLICATION_ENV constant to point at 'production'. 
 
 	vim lynx/public/.htaccess
   
   Change:
   	SetEnv APPLICATION_ENV development
   to:
   	SetEnv APPLICATION_ENV production
 
 * Point your browser at the lynx directory (http://server.example.com/lynx/) and
   you should be able to log in and use Lynx.