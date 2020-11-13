#!/usr/bin/env bash

if [ $# -le 3 ]; then
	echo "Usage: $0 host port tmpFolder sqliteDbFile initFile"
	exit 1
fi

base=$(dirname "$(readlink -f "$0")")

root=$1
host=$2
port=$3
tmpFolder=$4
db="$tmpFolder/$5"
pidFile="$base/.pid"
log="$tmpFolder/server.log"
initFile=${6:-""}
shutdownFile=${7:-""}

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
export TESTING_SERVER_ROOT=${root}

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
rm ${pidFile} > /dev/null 2>&1

if [ ! -z $shutdownFile ]; then
  php $shutdownFile $db
else
  rm ${db} > /dev/null 2>&1
fi
