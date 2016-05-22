# BeansBooks (eVAL Fork)

This is an unofficial fork of the BeansBooks project to introduce new features, 
better performance, and more updated library support.

## Noteable Changes

### Kohana Base Framework

Kohana has been upgraded to 3.3.5; all supporting modules have been upgraded to the newest versions along with it.

### PHP 7.0 Support

This version of Beans was developed on PHP 7.0 and should work on PHP 5.4.0 - 7.0.6.
This addresses the upstream bug of https://github.com/system76/beansbooks/issues/269.

### MariaDB Support

Along with PHP 7 and the removal of the outdated mysql library,
support for php-mysqli allows use for MariaDB and MySQL 10.1 without problem.

### Site Logo

The site logo now displays on every page to provide better and more consistent branding for your business.

### Site Themes/Colours!

No longer are you stuck with just one color for the site.  You can pick between TWO colors now!
(More to come shortly, I just needed to get something out the door to get back on schedule.)

### Relative Path Fixes

The entire Beans codebase has been updated to allow installation in a subdirectory instead of just the root path!
This means that it can be installed along-side other applications much easier.
This addresses the upstream bug https://github.com/system76/beansbooks/issues/238.

### Increased Length on Code/Aux/Alt/Ref

All code, aux, alt, and ref fields have been increased from 16 characters to 32 characters.
This is useful for PO numbers, transaction numbers, etc.

Since we import data directly from Amazon, the 16 character was simply too limiting.

### Customers as individuals OR companies

We have changed the logic to allow a customer to be created with ONLY the company_name.
This allows for customers that are companies to be created.


## Getting Started

This guide will walk you through getting a local instance of BeansBooks running. 
This is useful for development and testing, but should not be followed strictly 
for running a live environment.  In order to get started, you'll need the 
following:  

  *  Apache 2
  *  PHP 5.4+
  *  MySQL 5+ (MySQL 5.6 or MariaDB 10.1 Recommended)

On Ubuntu, you can run the following to get up to speed:  

    sudo apt-get update  
    sudo apt-get install apache2 php5 libapache2-mod-php5 php5-cli php5-mysql php5-mcrypt php5-gd mysql-server mysql-client git  
  
Once you've installed all of the prerequesites, create a directory where you 
want the source to reside, then download the code from git into that directory. 
The following will create a directory called 'source' within your home directory 
and install BeansBooks there.

    cd ~
    mkdir source
    cd source
    git clone --recursive git@github.com:eVAL-Agency/beansbooks.git
    cd beansbooks

Copy the example.htaccess file to .htaccess within your working directory

    cp example.htaccess .htaccess

If you are not planning on hosting with SSL, then we need to comment out two
lines in the .htaccess file.  Open the file for editing:

    vim .htaccess


It is strongly adviced to use SSL for this site, but if you need to disable it, 
look for the following two lines:

    RewriteCond %{HTTPS} !=on
    RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

and add a # character before them:

    #RewriteCond %{HTTPS} !=on
    #RewriteRule ^ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]


If you want to use the web user as the main owner of the files, a simple `chown www-data` will do the trick.
Otherwise ensure that the web user can write to `application/cache`, `application/logs`, and `application/config`. 

You should now have everything you need to run BeansBooks locally.  Next, we'll 
configure and setup several dependencies to enable your application to run.

## Configuring Packages

Before configuring BeansBooks itself, we need to setup the environment to run 
it. We're going to quickly setup a local MySQL database, Apache Virtual Host, 
and create the correct permissions on our code.

### MySQL

When setting up the packages in "Getting Started" above, you should have been 
prompted to create a root password for MySQL.  You'll need this for the next 
set of steps.  Run the following to connect to MySQL - you should provide the 
password that you created earlier when prompted.

    mysql -h localhost -u root -p

Next - enter the following lines one by one.  Please note - this sets the 
password for your database user to "beansdb" and should probably be changed. 
Go ahead and replace "beansdb" with a strong password.

    CREATE USER 'beans'@'localhost' IDENTIFIED BY  'beansdb';  
    GRANT USAGE ON * . * TO  'beans'@'localhost' IDENTIFIED BY  'beansdb' WITH MAX_QUERIES_PER_HOUR 0 MAX_CONNECTIONS_PER_HOUR 0 MAX_UPDATES_PER_HOUR 0 MAX_USER_CONNECTIONS 0 ;  
    CREATE DATABASE IF NOT EXISTS  `beans` CHARACTER SET utf8 COLLATE utf8_general_ci;
    GRANT ALL PRIVILEGES ON `beans`.* TO 'beans'@'localhost';
    exit  

Great!  Now you've setup your database and user.  Please make a note of the 
username ( beans ) and password you set above.  

### Apache

First things first, enable Mod_Rewrite:

    sudo a2enmod rewrite

Now we're going to setup Apache to serve BeansBooks locally.  In order to 
determine where are going to set our document root, we need to run the following 
in a terminal:  

    pwd

Whatever the output of that is - make a note of it.  It will be the "document 
root" for your virtual host.

We're going to setup our instance of BeansBooks to be found at http://beansbooks/ - 
this is convenient as it will neither interfere with an actual domain, and 
can be configured fairly easily.  Go ahead and run the following command:  

    sudo vim /etc/apache2/sites-available/beansbooks.conf

That will open a text editor for a new virtual host configuration - go ahead and 
copy and paste the following into the file.  Make sure to replace PWDHERE with 
the result of running "pwd" above - it will probably looking something like 
/home/yourusername/source/beansbooks and should be inserted without any trailing / .  

**TIP: To paste into the editor that you've opened, use Control + Shift + "v"**

    <VirtualHost *:80>
        ServerName beansbooks 
        ServerAlias beansbooks 

        DocumentRoot PWDHERE            
        <Directory PWDHERE>
            Options FollowSymLinks
            AllowOverride All
            Order allow,deny
            allow from all
        </Directory>
    </VirtualHost>

**If you're using Apache 2.4 or newer you should use the following instead.**

    <VirtualHost *:80>
        ServerName beansbooks 
        ServerAlias beansbooks 

        DocumentRoot PWDHERE            
        <Directory PWDHERE>
            Options FollowSymLinks
            AllowOverride All
            Require all granted
        </Directory>
    </VirtualHost>

After pasting in and editing the above code, hit Control + "x" to exit. If it prompts you 
to save your changes, hit "y".  Then run the following to disable the default virtual host, 
enable the beansbooks.conf virtual host and reload the Apache configuration.  

    sudo a2dissite 000-default
    sudo a2ensite beansbooks.conf
    sudo systemctl restart apache2
  
  
## Installation

At this point you should be able to navigate to https://SITENAME.TLD/install to finish the installation
process.

## SSL Support

If you would like to serve your instance of BeansBooks over SSL, you just need to add SSL
support to your web server:

    sudo a2enmod ssl

Then go ahead and edit your virtual host to support SSL connections:

    sudo vim /etc/apache2/sites-available/beansbooks.conf

    <IfModule mod_ssl.c>
        <VirtualHost *:443>
            ServerName beansbooks
            ServerAlias beansbooks
            
            DocumentRoot PWDHERE            
            <Directory PWDHERE>
                Options FollowSymLinks
                AllowOverride All
                Order allow,deny
                allow from all
            </Directory>

            SSLEngine on

            SSLCertificateFile /path/to/ssl/mydomain.com.crt
            SSLCertificateKeyFile /path/to/ssl/mydomain.com.unlocked.key

            <FilesMatch "\.(cgi|shtml|phtml|php)$">
                SSLOptions +StdEnvVars
            </FilesMatch>

            BrowserMatch "MSIE [2-6]" nokeepalive ssl-unclean-shutdown downgrade-1.0 force-response-1.0
            BrowserMatch "MSIE [17-9]" ssl-unclean-shutdown
        </VirtualHost>
    </IfModule>

**Note - if you adjusted your VirtualHost above for Apache 2.4, you should do so here as well.**

When you're done making changes, make sure to restart Apache.

## Troubleshooting

### MCrypt

If PHP / Apache complain that you're missing mcrypt support, or that an algorithm isn't available - you likely need to manually enable the mcrypt module:

```
sudo php5enmod mcrypt
```

Make sure to restart Apache afterwards and it should be resolved.
