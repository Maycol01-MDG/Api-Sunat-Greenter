#!/bin/sh
set -eu

port="${PORT:-80}"

sed -i "s/Listen [0-9]*/Listen ${port}/" /etc/apache2/ports.conf
sed -i "s#<VirtualHost \*:[0-9]*>#<VirtualHost *:${port}>#" /etc/apache2/sites-available/000-default.conf

exec apache2-foreground
