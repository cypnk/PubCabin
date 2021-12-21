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

# Backup and upload folders
mkdir -p backup
mkdir -p uploads

# Runtime application data
mkdir -p cache/workspaces/collections/categories/entries
mkdir -p cache/static
mkdir -p cache/volatile
mkdir -p modules

echo "	- Folders created" >> setup.log

# Make a backup if a database exists, instead of overwriting
if [ -f config.db ]; then
	sqlite3 config.db .dump > backup/config-$DATE.sql
	echo "	- Backed up config.db" >> setup.log
else
	sqlite3 config.db < config.sql
	echo "	- Created config.db" >> setup.log
fi

if [ -f main.db ]; then
	sqlite3 main.db .dump > backup/site-$DATE.sql
	echo "	- Backed up main.db" >> setup.log
else
	sqlite3 main.db < main.sql
	echo "	- Created main.db" >> setup.log
	
	sqlite3 main.db < main_install.sql
	echo "	- Installed data in main.db" >> setup.log
fi

if [ -f filter.db ]; then
	sqlite3 filter.db .dump > backup/filter-$DATE.sql
	echo "	- Backed up filter.db" >> setup.log
else
	sqlite3 filter.db < filter.sql
	echo "	- Created filter.db" >> setup.log
fi

if [ -f sessions.db ]; then
	sqlite3 sessions.db .dump > backup/sessions-$DATE.sql
	echo "	- Backed up sessions.db" >> setup.log
else
	sqlite3 sessions.db < sessions.sql
	echo "	- Created sessions.db" >> setup.log
fi

if [ -f cache.db ]; then
	sqlite3 cache.db .dump > backup/cache-$DATE.sql
	echo "	- Backed up cache.db" >> setup.log
else
	sqlite3 cache.db < cache.sql
	echo "	- Created cache.db" >> setup.log
fi

if [ -f logs.db ]; then
	sqlite3 logs.db .dump > backup/logs-$DATE.sql
	echo "	- Backed up logs.db" >> setup.log
else
	sqlite3 logs.db < logs.sql
	echo "	- Created logs.db" >> setup.log
fi

if [ -f firewall.db ]; then
	sqlite3 firewall.db .dump > backup/firewall-$DATE.sql
	echo "	- Backed up firewall.db" >> setup.log
else
	sqlite3 firewall.db < firewall.sql
	echo "	- Created firewall.db" >> setup.log
fi

# If a user is supplied and exists, set as owner
if id "$W_USER" >/dev/null 2>&1; then
	chown -R $W_USER backup
	chown -R $W_USER uploads
	
	chown $W_USER logs.db
	chown $W_USER config.db
	chown $W_USER main.db
	chown $W_USER filter.db
	chown $W_USER sessions.db
	chown $W_USER cache.db
	chown $W_USER errors.log
	
	echo "Ownership set for $W_USER" >> setup.log
	
	# Set permissions
	chmod -R 0600 backup
	chmod -R 0755 uploads
	chmod -R 0755 cache
	chmod -R 0755 modules
	
	chmod 0755 logs.db
	chmod 0755 config.db
	chmod 0755 main.db
	chmod 0755 filter.db
	chmod 0755 sessions.db
	chmod 0755 cache.db
	chmod 0755 errors.log
	chmod 0755 firewall.db
	
	# Custom config
	if [ -f config.json ]; then
		chown $W_USER config.json
		chmod 0755 config.json
	fi
	
	echo "	- Permissions set for $W_USER" >> setup.log
else
	echo "	- Skipping user permissions" >> setup.log
fi

exit

# To use with custom user:
# sh setup.sh www


