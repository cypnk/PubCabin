#!/bin/bash

# Make a backup of the database
# Suitable for cron

DATE=`date +%Y-%m-%d-%H-%M-%S`

# Prepare backup folder if it doesn't exist
mkdir -p backup

if [ -f config.db ]; then
	sqlite3 config.db .dump > backup/config-$DATE.sql
fi

if [ -f config.json ]; then
	cp config.json > backup/config-$DATE.json
fi

if [ -f main.db ]; then
	sqlite3 main.db .dump > backup/site-$DATE.sql
fi

if [ -f filter.db ]; then
	sqlite3 filter.db .dump > backup/filter-$DATE.sql
fi

if [ -f sessions.db ]; then
	sqlite3 sessions.db .dump > backup/sessions-$DATE.sql
fi

if [ -f cache.db ]; then
	sqlite3 cache.db .dump > backup/cache-$DATE.sql
fi

if [ -f logs.db ]; then
	sqlite3 logs.db .dump > backup/logs-$DATE.sql
fi

if [ -f firewall.db ]; then
	sqlite3 firewall.db .dump > backup/firewall-$DATE.sql
fi

exit
