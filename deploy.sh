#!/bin/sh

# Test server installation helper
# Set the web user and group (used by PHP to read/write to these)
# On Arch linux:
W_USER=http

# On OpenBSD:
#W_USER=www

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

chown -R $W_USER $SVR/data
chmod -R 0755 $SVR/data

cd $SVR/data

chmod +x backup.sh
chmod +x setup.sh
sh setup.sh

exit
