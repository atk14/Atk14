#!/bin/bash
#
# This script installs and initializes ATK14 in working directory.
# Also creates required databases and roles in Postgresql, configures and restarts Apache.
#
# Root's rights are required for some operations.
# So you'll be asked for your sudo password.
#
# Works on Ubuntu 10.10
#
# Usage:
#  $ mkdir myapp
#  $ cp myapp
#  $ atk14_init.sh

random_string () {
  MATRIX="0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz"
  LENGTH=$1

  n=0
  while [ "$n" -lt "$LENGTH" ]
  do
    PASS="$PASS${MATRIX:$(($RANDOM%${#MATRIX})):1}"
    let n+=1
  done

  echo $PASS
}

# setting up some initials
PWD=`pwd`                                 # /home/joe/webapps/myapp
APPNAME=`basename "$PWD"`                 # myapp
PG_USER=postgres                          # Postgresql's service account name
DBNAME_DEVEL="${APPNAME}_devel"           # myapp_devel
DBNAME_TEST="${APPNAME}_test"             # myapp_test
PG_PASSWORD=`random_string 6`
SECRET_TOKEN=`random_string 64`
FORCE=

while [ -n "$1" ]
do
  case $1 in
    "-f") FORCE=-f ;;
    *) break      ;;
        esac
    shift
done

if [ "$PG_PASSWORD" = "" ]; then
  echo "ERROR: unable to create a random string, kind of weird version of bash or something?"
  exit 1
fi

echo "Your new ATK14 app will be >> $APPNAME <<"
echo "Two new databases will be created in Postgresql: $DBNAME_DEVEL and $DBNAME_TEST"
echo "Two new users will be created in Postgresql: $DBNAME_DEVEL and $DBNAME_TEST"
echo "New virtual host will be configured in Apache: $APPNAME.localhost"
echo "New record will be added to /etc/hosts: 127.0.0.1 $APPNAME.localhost"
echo "And even more you will be asked for your sudo password!"

if [ -z "$FORCE" ] ; then
  echo -n "Sounds good to you? (press y if so) "
  read confirm
  if [ "$confirm" != "y" ]; then
    exit 1
  fi
  files_in_current_directory=`ls -a | wc -l`
  if [ $files_in_current_directory != "2" ]; then
        echo -n "Current working dir is not empty, really continue? (press c if so) "
  read confirm
    if [ "$confirm" != "c" ]; then
        exit 2
    fi
  fi
fi

echo ""
echo "About to download ATK14 source code"
echo "-----------------------------------"
if [ ! -e atk14 ] ; then
  git clone --recursive git://github.com/yarri/Atk14.git ./atk14 &&
  mv atk14/skelet/.htaccess ./ &&\
  mv atk14/skelet/* ./ &&\
  rmdir atk14/skelet
  echo "This is README for ${APPNAME}" > ./README.md

  # removing git's working files and dirs
  find ./ -name '.git*' -type f -exec rm {} \;
  find ./ -name '.git' -type d -exec rm -rf {} \;

  chmod -R 777 ./tmp
  touch ./log/application.log
  chmod 666 ./log/application.log
  touch ./log/test.log
  chmod 666 ./log/test.log
fi

sed -i "s/password: p/password: $PG_PASSWORD/" config/database.yml
sed -i "s/atk14_devel/$DBNAME_DEVEL/" config/database.yml
sed -i "s/atk14_test/$DBNAME_TEST/" config/database.yml

sed -i "s/put_some_random_string_here/$SECRET_TOKEN/" config/settings.php
sed -i "s/myapp.localhost/$APPNAME.localhost/" config/settings.php
sed -i "s/www.myapp.com/www.$APPNAME.com/" config/settings.php

echo ""
echo "About to create & initialize database"
echo "-------------------------------------"
PGPASS=
for environ in DEVELOPMENT TEST ; do
  echo "Setting $environ environment"
  for cmd in create_database initialize_database migrate ; do
    echo "Calling $cmd"
    ATK14_ENV=$environ ./scripts/$cmd $FORCE || (
      echo
      echo "SCRIPT atk14/src/scripts/$cmd FAILED !!!"
      exit 3
    )
   done
   PGPASS="$PGPASS`ATK14_ENV=$environ ./scripts/pgpass_record`
"
done

echo
echo "About to configure apache"
echo "-------------------------"

./scripts/virtual_host_configuration -f

echo
echo "You are advised to add these lines to ~/.pgpass"
echo "$PGPASS"

echo
echo "Happy coding (would be nice to never see you in madhouse)"

exit 0
