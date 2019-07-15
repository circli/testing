#!/usr/bin/env bash

if [ $# -le 4 ]; then
	echo "Usage: $0 host port tmpFolder sqliteDbFile"
	exit 1
fi

base=$(dirname "$(readlink -f "$0")")

host=$1
port=$2
tmpFolder=$3
db="$tmpFolder/$4"
pidFile="$base/.pid"
log="$tmpFolder/server.log"
initFile=${5:-""}

if [ -f ${pidFile} ]; then
	pid=$(cat ${pidFile})
	if [ -d /proc/${pid} ]; then
		echo "Error: Seems there is a server already running.!"
		exit 2
	fi
	rm ${pidFile}
fi

export TESTING_DB=${db}
export TESTING_INIT_FILE=${initFile}

php -S ${host}:${port} -t ${base} >> ${log} 2>&1 &
echo $! > ${pidFile}
pid=$(cat ${pidFile})

# 200ms waiting time to see if it started
sleep 0.2

if [ ! -d /proc/${pid} ]; then
	echo "Could not start php server! See $log for information"
	exit 3
fi

wait ${pid}
rm ${db} ${pidFile} > /dev/null 2>&1
