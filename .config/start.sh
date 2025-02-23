#!/bin/bash

# Start php-fpm in the background
php-fpm &

# Start php artisan schedule:work
php artisan schedule:work
