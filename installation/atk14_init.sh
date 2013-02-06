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
APPNAME=`basename "$PWD" | sed -r 's/[^a-z0-9]/ /gi' | sed -r 's/^ +//' | sed -r 's/ +$//g' | sed -r 's/ {2,}/ /g'` # myapp
DOMAIN=`echo -n $APPNAME | tr '[A-Z] ' '[a-z]-'`
DBNAME=`echo -n $APPNAME | sed -r 's/^[0-9 ]+//' | tr '[A-Z] ' '[a-z]_'` # database name should not start with a number

PG_USER=postgres                         # Postgresql's service account name
DBNAME_DEVEL="${DBNAME}_devel"           # myapp_devel
DBNAME_TEST="${DBNAME}_test"             # myapp_test
DBNAME_PRODUCTION="${DBNAME}_production" # myapp_production
PG_PASSWORD=`random_string 8`
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
  echo "ERROR: Unable to create a random string. Kind of weird version of bash or something?"
  exit 1
fi

if [ "$APPNAME" = "" ]; then
  echo "ERROR: Unable to determine application's name. Perhaps you are installing applicaton in a kind of strange directory."
  exit 1
fi

if [ "$DBNAME" = "" ]; then
  echo "ERROR: Unable to determine database's name. Perhaps you are installing applicaton in a kind of strange directory."
  exit 1
fi

echo "Your new ATK14 app will be >> $APPNAME <<"
echo "Two new databases will be created in Postgresql: $DBNAME_DEVEL and $DBNAME_TEST"
echo "Two new users will be created in Postgresql: $DBNAME_DEVEL and $DBNAME_TEST"
echo "New virtual host will be configured in Apache: $DOMAIN.localhost"
echo "New record will be added to /etc/hosts: 127.0.0.1 $DOMAIN.localhost"
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
sed -i "s/atk14_production/$DBNAME_PRODUCTION/" config/database.yml
sed -i "s/ATK14 Project/$APPNAME/" config/settings.php # defines ATK14_APPLICATION_NAME

sed -i "s/put_some_random_string_here/$SECRET_TOKEN/" config/settings.php
sed -i "s/myapp.localhost/$DOMAIN.localhost/" config/settings.php
sed -i "s/www.myapp.com/www.$DOMAIN.com/" config/settings.php

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
