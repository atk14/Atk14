How to install ATK14
--------------------

This document describes installation ATK14 for development on Ubuntu 10.10 or higher.

## Requirements

#### Apache webserver

You need one to be installed :)


    $ sudo apt-get install apache2-mpm-prefork


Mod Rewrite has to be enabled.

    $ cd /etc/apache2/mods-enabled
    $ sudo ln -s ../mods-available/rewrite.load ./

#### Git

You need Git to checkout ATK14 source codes from http://github.com

    $ sudo apt-get install git

#### PHP

    $ sudo apt-get install php5 php5-cli

#### Postgresql

You need Postgresql server to be installed on you system. Install also the postgresql PHP extension.

    $ sudo apt-get install postgresql php5-pgsql

Postgresql access control file pg_hba.conf should look like this. The file may be found at /etc/postgresql/8.4/main/pg_hba.conf

    # TYPE  DATABASE    USER       CIDR-ADDRESS  METHOD
    local   all         postgres                 ident
    host    all         postgres   127.0.0.1/32  ident
    host    all         postgres   ::1/128       ident
    local   sameuser    all                      md5
    host    sameuser    all        127.0.0.1/32  md5
    host    sameuser    all        ::1/128       md5

These lines say that administer (postgres) can connect to any database but only when he is logged as postgres in the system, other user can connect only to a database with the same name and must provide a correct password.

Now restart the server.

    $ sudo service postgresql restart
		or
    $ sudo /etc/init.d/postgresql restart

#### Gettext

If you are planning to develop a multilanguage application, you need Gettext to be installed.

    $ sudo apt-get install gettext php-gettext

Great tool for edition *.po files is Poedit.

    $ sudo apt-get install poedit

Be sure that /var/lib/locales/supported.d/local contains lines:

    en_US.UTF-8 UTF-8
    cs_CZ.UTF-8 UTF-8

If it doesn't add these locales and then run:

    $ sudo locale-gen

## Installation

Now you are ready to install ATK14 skelet for your new web application. I presume that Apache has the Document Root at /var/www/. So /var/www/myapp/ should be the fine folder for your app.

    $ cd /var/www/
    $ sudo wget -O atk14_init.sh https://raw.github.com/yarri/Atk14/master/installation/atk14_init.sh
    $ export MY_ID=`id -u`
    $ sudo mkdir myapp
    $ sudo chown $MY_ID myapp
    $ cd myapp
    $ bash ../atk14_init.sh

Run browser and point to http://myapp.localhost/. If it's working than congrats!

For a next application you should omit the Requirements chapter.
