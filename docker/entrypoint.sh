#!/bin/sh

usermod -ou ${HOST_USER_ID} app
groupmod -og ${HOST_GROUP_ID} app

gosu app docker-php-entrypoint "$@"
