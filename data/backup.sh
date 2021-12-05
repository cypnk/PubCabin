#!/bin/bash

# Make a backup of the database
# Suitable for cron

DATE=`date +%Y-%m-%d-%H-%M-%S`

touch backup.log

# Status
echo "\n\nRunning backup $DATE" >> backup.log

# Prepare backup folder if it doesn't exist
mkdir -p backup

if [ -f config.db ]; then
	sqlite3 config.db .dump > backup/config-$DATE.sql
	echo "	- Backed up config.db" >> backup.log
fi

if [ -f config.json ]; then
	cp config.json > backup/config-$DATE.json
	echo "	- Backed up config.json" >> backup.log
fi

if [ -f main.db ]; then
	sqlite3 main.db .dump > backup/site-$DATE.sql
	echo "	- Backed up main.db" >> backup.log
fi

if [ -f filter.db ]; then
	sqlite3 filter.db .dump > backup/filter-$DATE.sql
	echo "	- Backed up filter.db" >> backup.log
fi

if [ -f sessions.db ]; then
	sqlite3 sessions.db .dump > backup/sessions-$DATE.sql
	echo "	- Backed up sessions.db" >> backup.log
fi

if [ -f cache.db ]; then
	sqlite3 cache.db .dump > backup/cache-$DATE.sql
	echo "	- Backed up cache.db" >> backup.log
fi

if [ -f logs.db ]; then
	sqlite3 logs.db .dump > backup/logs-$DATE.sql
	echo "	- Backed up logs.db" >> backup.log
fi

if [ -f firewall.db ]; then
	sqlite3 firewall.db .dump > backup/firewall-$DATE.sql
	echo "	- Backed up firewall.db" >> backup.log
fi

exit
