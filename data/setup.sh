#!/bin/bash

# Create the databases from schema files and create folders

# Set the web user and group (used by PHP to read/write to these)
# W_USER=www

# Timestamp
DATE=`date +%Y-%m-%d-%H-%M-%S`

# Backup and upload folders
mkdir -p backup
mkdir -p uploads

# Language folder
mkdir -p lang

# Custom config
touch config.json

# Default language file
touch lang/en-US.json


# Make a backup if a database exists, instead of overwriting
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


# If a user is supplied, set as owner
if [ -n "$W_USER" ]; then
	chown -R $W_USER backup
	chown -R $W_USER uploads
	chown -R $W_USER lang
	
	chown $W_USER main.db
	chown $W_USER filter.db
	chown $W_USER sessions.db
	chown $W_USER cache.db
	chown $W_USER config.json
fi


# Set permissions
chmod -R 600 backup
chmod -R 755 uploads
chmod -R 755 lang

chmod 755 main.db
chmod 755 filter.db
chmod 755 sessions.db
chmod 755 cache.db
chmod 755 config.json

exit


