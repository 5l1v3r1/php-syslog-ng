#!/bin/sh
# The following command will generate:
# 50 messages per second
# using TCP stream
# for one minute (60 seconds)
# on port 5000 (check /etc/syslog-ng.conf to make sure it's listening)

#######################################
# Prompt for a value
#######################################
f_ANSWER()
{
   	printf "%s " "$1"
   	if [ "$2" != "" ] ; then
	   	printf "[%s] " "$2"
   	fi 
	if [ "${DEFAULT:-0}" -eq 0 ] ; then
	   	read ANSWER
   	else
	   	printf "%s\n" "$2"
   	fi
   	if [ "$ANSWER" = "" ] ; then
	   	ANSWER="$2"
   	fi
}

f_ANSWER "Logs per day?" "10000000"
LPD=$ANSWER
MPS=`expr $LPD / 24 / 60 / 60`
echo "Will run @ $MPS messages per second"
f_ANSWER "Destination Host?" "log1"
HOST=$ANSWER
f_ANSWER "Port?" "5000"
PORT=$ANSWER
f_ANSWER "How Long? (seconds)" "86400"
TIME=$ANSWER

echo "Starting loggen"
echo "MPS: $MPS"
echo "TIME: $TIME"
echo "HOST: $HOST"
echo "PORT: $PORT"
./loggen -r $MPS -S --interval $TIME $HOST $PORT
echo "Run completed!"
exit
