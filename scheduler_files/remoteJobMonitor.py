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
import requests
import sys
import os
import json
import psycopg2 as psg
import time
import subprocess

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']
ftp=config['localftp']
ftpuser=ftp['username']
ftppass=ftp['password']
ftpdomain=ftp['domain']

jobid=sys.argv[1]
teskEndpoint=sys.argv[2]
logPath=sys.argv[3]

teskUrl=teskEndpoint + '/v1/tasks/' + jobid + '?view=FULL'

headers={'Accept':'application/json', 'Content-Type': 'application/json'}
response = requests.get(teskUrl,headers=headers)

body=json.loads(response.content)

status=body['state']


while (status!='COMPLETE') and (status!='EXECUTOR_ERROR') and (status!='CANCELED') and (status!='SYSTEM_ERROR'):
    time.sleep(5)
    response = requests.get(teskUrl,headers=headers)
    body=json.loads(response.content)
    status=body['state']

logs=body['logs'][0]





if (status=='EXECUTOR_ERROR'):
    sql="UPDATE run_history SET start='" + start +  "', stop=NOW(), status='Error' WHERE jobid='" + jobid + "'"
    start=logs['logs'][0]['task_started']
    stdout=logs['logs'][0]['stdout']
    #write logs
    logfile=logPath + '/' + 'logs.txt'
    g=open(logfile,'w')    
    g.write(logs + '\n')
    g.close()

if (status=='CANCELED'):
    sql="UPDATE run_history SET start='" + start +  "', stop=NOW(), status='Canceled' WHERE jobid='" + jobid + "'"


if (status=='COMPLETE'):
    # Uncomment the following two lines if you want the 
    # net compoutation time, excluding download/upload of i/o
    # start=logs[logs][0]['start_time']
    # start=logs[logs][0]['end_time']
    # Uncomment the following two lines if you want the 
    # net compoutation time, excluding download/upload of i/o
    start=logs['logs'][0]['start_time']
    stop=logs['logs'][0]['end_time']

    # Uncomment the following two lines if you want the 
    # total time, including download/upload of i/o
    # start=logs['start_time']
    # start=logs['end_time']

    #write logs
    stdout=logs['logs'][0]['stdout']
    logfile=logPath + '/' + 'logs.txt'
    g=open(logfile,'w')    
    g.write(stdout + '\n')
    g.close()

    # collecr the task info and
    # clean up tesk jobs after keeping their logs
    subtasks=[jobid, jobid+'-ex-00', jobid + '-outputs-filer', jobid + '-inputs-filer']
    for subtask in subtasks:
        kube_command='kubectl -n tesk delete job ' + subtask
        try:
            logs=subprocess.check_output(kube_command,stderr=subprocess.STDOUT, shell=True)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
            exit(4)

   

    sql="UPDATE run_history SET start='" + start +  "', stop='" + stop + "', status='Complete'" +  "WHERE jobid='" + jobid + "'"

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
cur.execute(sql)
conn.commit()
conn.close()