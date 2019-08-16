#!/usr/bin/env bash

composer install --optimize-autoloader --no-dev &&

source <(php bin/pre-build-console pre-build:parse-variables)

export BUILD_NUMBER=666 &&
php robo.phar build:deb
