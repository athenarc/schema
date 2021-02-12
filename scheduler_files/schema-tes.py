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
import sys
import tesInputFiler
import tesOutputFiler
import tesConfigFileCreator
import tesCleaner
import tesJobMonitor
import json
import psycopg2 as psg
import os.path
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

dataFile=sys.argv[1]
folder=sys.argv[2]
jobid=sys.argv[3]
nfsIp=sys.argv[4]
maxCores=sys.argv[5]
maxMem=sys.argv[6]

# 
# Auxiliary function to change job status
#
def updateJobStatus(code,jobid,config):
    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()

    query="UPDATE run_history SET remote_status_code='" + code + "' WHERE jobid='" + jobid + "'"
    
    cur.execute(query)
    conn.commit()
    conn.close()


####
# Main code starts here
####

#
# Read the json from the data file
#
f=open(dataFile,'r')
try:
    data=json.load(f)
except:
    updateJobStatus('-1',config)
    exit(1)
f.close()

#
# Available status codes:
# 2: Initializing
# 3: Running
# 4: Complete or Canceled
# -2..-8: System error
# -9: Executor Error
# -10: Canceled

updateJobStatus('2',jobid,config)
returnCode,mounts,referenceMounts=tesInputFiler.getInputs(data,folder,config)

if returnCode!=0:
    updateJobStatus('-2','Error',jobid,config)
    exit(returnCode)

returnCode,yamlFile,jobName=tesConfigFileCreator.createFile(data,mounts+referenceMounts,folder,jobid,nfsIp,maxCores,maxMem)

if returnCode!=0:
    updateJobStatus('-3',jobid,config)
    exit(returnCode)

updateJobStatus('3',jobid,config)
returnCode=subprocess.call(['kubectl','apply','-f',yamlFile])

if returnCode!=0:
    updateJobStatus('-4',jobid,config)
    exit(returnCode)

returnCode=tesJobMonitor.monitorJob(jobName,jobid)
if returnCode!=0:
    updateJobStatus('-5',jobid,config)
    #
    # On error clean job
    #
    tesCleaner.cleanJob(mounts,folder,jobName)
    subprocess.call(['kubectl','delete','-f',yamlFile])
    exit(returnCode)

returnCode=tesOutputFiler.uploadOutput(data,config,folder)

if returnCode!=0:
    updateJobStatus('-6',jobid,config)
    exit(returnCode)

updateJobStatus('4',jobid,config)
returnCode=tesCleaner.cleanJob(mounts,folder,jobName)

if returnCode!=0:
    updateJobStatus('-7',jobid,config)
    exit(returnCode)

returnCode=subprocess.call(['kubectl','delete','-f',yamlFile])

if returnCode!=0:
    updateJobStatus('-8',jobid,config)
    exit(returnCode)