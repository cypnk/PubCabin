#!/bin/sh

# Test server installation helper

# Set the web user and group (used by PHP to read/write to these)
# On Arch linux, the web user is http. On OpenBSD, it's www
W_USER=${1:-http}

# Default deployment folder on Arch. On OpenBSD, /var/www/pubcabin
SVR=${2:-/srv/http/pubcabin}

# Timestamp
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Deploy folder
mkdir -p $SRV

# Create snapshots of existing content
mkdir -p $SVR/../snaps-$DATE
mv -b $SVR/* $SVR/../snaps-$DATE
cp -r -n * $SVR/

if id "$W_USER" >/dev/null 2>&1; then
	chown -R $W_USER $SVR/data
	chmod -R 0755 $SVR/data
fi

cd $SVR/data

chmod +x backup.sh
chmod +x setup.sh $W_USER
sh setup.sh $W_USER

exit

# To use with custom user and folder:
# sh deploy.sh www /var/www/pubcabin

