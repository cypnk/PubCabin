#!/bin/bash

# Make a backup of the database
# Suitable for cron

DATE=`date +%Y-%m-%d-%H-%M-%S`

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

exit
