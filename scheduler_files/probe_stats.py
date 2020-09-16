#!/usr/bin/python
import os
import sys
import subprocess
import time
import psycopg2 as psg
import json

name=sys.argv[1]
jobid=sys.argv[2]

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

jobName=name.lower() + '-' + jobid

command=['kubectl', 'get', 'pods', '--no-headers']
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)

podid=''
out=out.split('\n')
for line in out:
    line=line.split(' ')
    podid=line[0]
    if podid.startswith(jobName):
        break

if podid=='':
    exit(1)

cpu=0
memory=0
while(True):
    command=['kubectl', 'top', 'pod',podid, '--no-headers']
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT)
    except subprocess.CalledProcessError as exc:
        out=exc.output


    
    

    if 'NotFound' in out:
        command=['kubectl', 'get', 'pod', podid, '--no-headers']
        try:
            podInfo=subprocess.check_output(command,stderr=subprocess.STDOUT)
        except subprocess.CalledProcessError as exc:
            podInfo=exc.output
        if 'NotFound' in podInfo:
            break
        podTokens=podInfo.split(' ')
        status=podTokens[6].strip()
       # print(status)
        if status=='Completed':
            break
        if status=='Terminating':
            break

        continue
        # Metrics not available yet
    

    tokens=out.split(' ')
    # print(tokens)
    topmem=float(tokens[6][:-2])

    memmeasure=tokens[6][-2:]
    # print(topmem,memmeasure,topmem/1024)
    if memmeasure=='Mi':
        topmem/=1024.0
    elif memmeasure=='Ki':
        topmem/=(1024.0*1024)
    
    if memory<topmem:
        memory=topmem
    topcpu=int(tokens[3][:-1])
    if cpu<topcpu:
        cpu=topcpu

    time.sleep(1)

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
query="UPDATE run_history SET ram=" + str(memory) + ", cpu=" + str(cpu) + "WHERE jobid='" + jobid + "'"

cur.execute(query)
conn.commit()

conn.close()


