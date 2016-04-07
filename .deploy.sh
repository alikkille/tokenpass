#!/bin/bash

set -e

echo; echo "updating composer dependencies"
/usr/local/bin/composer.phar install --prefer-dist --no-progress

echo; echo "copying sample environment"
cp -v .env.example .env

echo; echo "compiling assets"
DIR=`pwd`
cd resources/assets;
npm cache clean;
# try up to 4 times
COUNTER=0
while [  $COUNTER -lt 4 ]; do
    let COUNTER=COUNTER+1 
    npm install && break
done


./node_modules/gulp/bin/gulp.js
cd $DIR
