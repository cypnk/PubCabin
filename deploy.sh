#!/bin/sh

# Test server installation helper

# On Arch linux:
SVR=/usr/share/nginx/pubcabin

# On OpenBSD:
#SVR=/var/www/pubcabin

# Timestamp
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Create snapshots of existing content
mkdir -p $SVR/../snaps-$DATE
mv -b $SVR/* $SVR/../snaps-$DATE
cp -r -n * $SVR/

cd $SVR/data

chmod +x backup.sh
chmod +x setup.sh
sh setup.sh

exit
