#!/bin/bash

# Create the databases from schema files and create folders

# Set the web user and group (used by PHP to read/write to these)
# On Arch linux, the web user is http, on OpenBSD, it's www:
W_USER=${1:-http}

# Timestamp
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Log files
touch errors.log
touch setup.log

# Status
echo "\n\nRunning setup $DATE" >> setup.log

# Snapshots
mkdir -p snaps

# Runtime application data

echo "	- Folders created" >> setup.log

if [ -f filter.db ]; then
	sqlite3 filter.db .dump > snaps/filter-$DATE.sql
	echo "	- Backed up filter.db" >> setup.log
else
	sqlite3 filter.db < filter.sql
	echo "	- Created filter.db" >> setup.log
fi

if [ -f cache.db ]; then
	sqlite3 cache.db .dump > snaps/cache-$DATE.sql
	echo "	- Backed up cache.db" >> setup.log
else
	sqlite3 cache.db < cache.sql
	echo "	- Created cache.db" >> setup.log
fi

if [ -f logs.db ]; then
	sqlite3 logs.db .dump > snaps/logs-$DATE.sql
	echo "	- Backed up logs.db" >> setup.log
else
	sqlite3 logs.db < logs.sql
	echo "	- Created logs.db" >> setup.log
fi

if [ -f firewall.db ]; then
	sqlite3 firewall.db .dump > snaps/firewall-$DATE.sql
	echo "	- Backed up firewall.db" >> setup.log
else
	sqlite3 firewall.db < firewall.sql
	echo "	- Created firewall.db" >> setup.log
fi

# If a user is supplied and exists, set as owner
if id "$W_USER" >/dev/null 2>&1; then
	chown -R $W_USER snaps
	
	chown $W_USER logs.db
	chown $W_USER filter.db
	chown $W_USER cache.db
	chown $W_USER errors.log
	
	echo "Ownership set for $W_USER" >> setup.log
	
	# Set permissions
	chmod -R 0600 snaps
	
	chmod 0755 logs.db
	chmod 0755 filter.db
	chmod 0755 cache.db
	chmod 0755 errors.log
	chmod 0755 firewall.db
	
	# Custom config
	if [ -f defaultconfig.json ]; then
		chown $W_USER defaultconfig.json
		chmod 0755 defaultconfig.json
	fi
	
	echo "	- Permissions set for $W_USER" >> setup.log
else
	echo "	- Skipping user permissions" >> setup.log
fi

exit

# To use with custom user:
# sh setup.sh www


