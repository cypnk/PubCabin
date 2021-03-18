#!/bin/bash

# Create the databases from schema files and create folders

# Set the web user and group (used by PHP to read/write to these)
# On Arch linux:
W_USER=http

# On OpenBSD:
# W_USER=www

# Timestamp
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Backup and upload folders
mkdir -p backup
mkdir -p uploads

# Runtime application data
mkdir -p cache
mkdir -p modules

# Language folder
mkdir -p lang

# Default language file
touch lang/en-US.json

# Error log file
touch errors.log

# Make a backup if a database exists, instead of overwriting
if [ -f config.db ]; then
	sqlite3 config.db .dump > backup/config-$DATE.sql
else
	sqlite3 config.db < config.sql
fi

if [ -f main.db ]; then
	sqlite3 main.db .dump > backup/site-$DATE.sql
else
	sqlite3 main.db < main.sql
fi

if [ -f filter.db ]; then
	sqlite3 filter.db .dump > backup/filter-$DATE.sql
else
	sqlite3 filter.db < filter.sql
fi

if [ -f sessions.db ]; then
	sqlite3 sessions.db .dump > backup/sessions-$DATE.sql
else
	sqlite3 sessions.db < sessions.sql
fi

if [ -f cache.db ]; then
	sqlite3 cache.db .dump > backup/cache-$DATE.sql
else
	sqlite3 cache.db < cache.sql
fi

if [ -f logs.db ]; then
	sqlite3 logs.db .dump > backup/logs-$DATE.sql
else
	sqlite3 logs.db < logs.sql
fi

# If a user is supplied and exists, set as owner
if id "$W_USER" >/dev/null 2>&1; then
	chown -R $W_USER backup
	chown -R $W_USER uploads
	chown -R $W_USER lang
	
	chown $W_USER logs.db
	chown $W_USER config.db
	chown $W_USER main.db
	chown $W_USER filter.db
	chown $W_USER sessions.db
	chown $W_USER cache.db
	
	# Set permissions
	chmod -R 0600 backup
	chmod -R 0755 uploads
	chmod -R 0755 cache
	chmod -R 0755 modules
	chmod -R 0755 lang
	
	chmod 0755 logs.db
	chmod 0755 config.db
	chmod 0755 main.db
	chmod 0755 filter.db
	chmod 0755 sessions.db
	chmod 0755 cache.db
	chmod 0755 errors.log
	
	if [ -f config.json ]; then
		chown $W_USER config.json
		chmod 0755 config.json
	fi
fi

exit


