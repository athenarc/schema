#!/usr/bin/python3
####################################################################################
#
#  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
#  for the Information Management Systems Institute, "Athena" Research Center.
#  
#  This file is part of SCHeMa.
#  
#  SCHeMa is free software: you can redistribute it and/or modify
#  it under the terms of the GNU General Public License as published by
#  the Free Software Foundation, either version 3 of the License, or
#  (at your option) any later version.
#  
#  SCHeMa is distributed in the hope that it will be useful,
#  but WITHOUT ANY WARRANTY; without even the implied warranty of
#  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
#  GNU General Public License for more details.
#  
#  You should have received a copy of the GNU General Public License
#  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
#
####################################################################################
import os
import sys
import subprocess
import time
import requests
import psycopg2 as psg
import json
from datetime import datetime

jobid=sys.argv[1]
folder=sys.argv[2]
endpoint=sys.argv[3]
tesType=sys.argv[4]

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()
db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

cpu=0
memory=0

if tesType=='1':
    taskUrl=endpoint + '/v1/tasks?id=' + jobid + '&view=FULL'
else:
    taskUrl=endpoint + '/v1/tasks/' + jobid + '?view=FULL'

headers={'Accept':'application/json', 'Content-Type': 'application/json'}
response = requests.get(taskUrl,headers=headers)
if not response.ok:
    print(2,'Job not found.')
    exit(2)
try:
    content=json.loads(response.content)
except Exception as e:
    print(3,e)
    exit(3)

state=content['state']
states=['UNKNOWN','QUEUED','INITIALIZING','RUNNING','PAUSED','COMPLETE','EXECUTOR_ERROR','SYSTEM_ERROR','CANCELED']
stopStates=['COMPLETE','CANCELED'];
errorStatues=['EXECUTOR_ERROR','SYSTEM_ERROR']
sqlStates={'COMPLETE':'Complete','EXECUTOR_ERROR':'Error','SYSTEM_ERROR':'Error','CANCELED':'Canceled'}

toleration=0;
while state not in stopStates:
    if tesType=='1':
        try:
            resources=content['current_resources']
            tmp_cpu=resources['cpu_cores']
            tmp_memory=resources['ram_gb']
            if memory<tmp_memory:
                memory=tmp_memory
            if cpu<tmp_cpu:
                cpu=tmp_cpu
        except:
            pass
    else:
        resources=content['resources']
        cpu=resources['cpu_cores']
        memory=resources['ram_gb']

    time.sleep(1)
    #
    # Get latest state
    #
    headers={'Accept':'application/json', 'Content-Type': 'application/json'}
    response = requests.get(taskUrl,headers=headers)
    if not response.ok:
        print(4,'Job not found by loop.')
        exit(4)
    try:
        content=json.loads(response.content)
    except Exception as e:
        print(5,e)
        exit(5)
    state=content['state']
    if state in errorStatues:
        if toleration>2:
            break
        toleration+=1

k8sformat="%Y-%m-%dT%H:%M:%S.%fZ"
sqlformat="%Y-%m-%d %H:%M:%S"
while 'end_time' not in content['logs'][0]:
    headers={'Accept':'application/json', 'Content-Type': 'application/json'}
    response = requests.get(taskUrl,headers=headers)
    if not response.ok:
        print(6,'Job not found by loop.')
        exit(6)
    try:
        content=json.loads(response.content)
    except Exception as e:
        print(7,e)
        exit(7)
    time.sleep(1)

endTime=content['logs'][0]['end_time']
startTime=content['logs'][0]['start_time']
endobj=datetime.strptime(endTime,k8sformat)
startobj=datetime.strptime(startTime,k8sformat)
status=sqlStates[state]

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
query="UPDATE run_history SET ram=" + str(memory) + ", cpu=" + str(cpu) + ", start='" + startobj.strftime(sqlformat) +"', stop='" + endobj.strftime(sqlformat) + "', status='" + status +"' WHERE jobid='" + jobid + "'"
cur.execute(query)
conn.commit()
conn.close()
try:
    logs=content['logs'][0]['logs'][0]['stdout']
except:
    logs=""

logFile=folder + '/logs.txt'
g=open(logFile,'w')
g.write(logs)
g.close()

if config['cleanTeskJobs']:
    namespaces=config.get('namespaces',None)
    jobNamespace=None
    if namespaces is not None:
        jobNamespace=namespaces.get('tesk',None)

    if jobNamespace is not None:
        command="for j in $(kubectl get jobs -n " + jobNamespace + " --no-headers | grep '" + jobid +  "' | tr -s ' ' | cut -d ' ' -f 1); do kubectl delete jobs -n " + jobNamespace + " $j; done"
    else:
        command="kubectl get jobs --no-headers -l | grep '" + jobid +  "' | tr -s ' '| cut -d ' ' -f 1); do kubectl delete jobs $j; done "
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        print(exc.output)

