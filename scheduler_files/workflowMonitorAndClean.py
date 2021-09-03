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
from ftplib import FTP, error_perm
import sys
import os
import re
import json
import time
import subprocess
import requests
import psycopg2 as psg


def updateStatus(status, jobid, start=None, stop=None, ram=None, cpu=None):

    sql="UPDATE run_history SET status=%s, start=%s, stop=%s, ram=%s, cpu=%s WHERE jobid=%s"

    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()
    cur.execute(sql,(status, start, stop, ram, cpu, jobid))
    conn.commit()
    conn.close()


configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'

try:
    configFile=open(configFileName,'r')
except FileNotFoundError as fnferr:
    print("ERROR:",fnferr)
    exit(5)

config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']
ftp=config['localftp']
ftpLocal=ftp['active']
ftpuser=ftp['username']
ftppass=ftp['password']
ftpdomain=ftp['domain']

namespaces=config.get('namespaces',None)
teskNamespace=None
if namespaces is not None:
    teskNamespace=namespaces.get('tesk',None)

try:
    jobid=sys.argv[1]
    wesEndpoint=sys.argv[2]
    teskEndpoint=sys.argv[3]
    outFolder=sys.argv[4]
    logPath=sys.argv[5]
except IndexError as ierr:
    print("ERROR:",ierr)
    print("Use: %s <JOBID> <WES_ENDPOINT> <TES_ENDPOINT> <OUT_FOLDER> <TMP_FOLDER>" % sys.argv[0])
    exit(1)

workflowUrl=wesEndpoint + '/ga4gh/wes/v1/runs/' + jobid

headers={'Accept':'application/json', 'Content-Type': 'application/json'}
response = requests.get(workflowUrl,headers=headers)

body=json.loads(response.content)
status=body['state']

while (status!='COMPLETE') and (status!='EXECUTOR_ERROR') and (status!='SYSTEM_ERROR') and (status!='CANCELED'):
    time.sleep(5)
    response = requests.get(workflowUrl,headers=headers)
    body=json.loads(response.content)
    runLog=body['run_log']
    start=runLog['task_started']
    old_status=status
    status=body['state']

    if(status!=old_status):
        updateStatus(status, jobid, start)

runLog=body['run_log']
taskLogs=body['task_logs']
outputs=body['outputs']

start=runLog['task_started']

if (status=='EXECUTOR_ERROR'):
    updateStatus('Error', jobid, start, 'NOW()')
    #sql="UPDATE run_history SET start='" + start +  "', stop=NOW(), status='Error' WHERE jobid='" + jobid + "'"
elif (status=='CANCELED' or status=='CANCELLING'):
    updateStatus('Canceled', jobid, start, 'NOW()')
    #sql="UPDATE run_history SET start='" + start +  "', stop=NOW(), status='Canceled' WHERE jobid='" + jobid + "'"
elif (status=='COMPLETE'):
    stop=runLog['task_finished']
    ram=0.0
    cpu=0.0
    taskIds={}
    i=1
    taskSteps={}

    #retrieve workflow outputs
    for output in outputs:
        if (isinstance(outputs[output],list)):
            for subOutput in outputs[output]:
                outClass=subOutput['class']
                name=subOutput['basename']
                url=subOutput['location']
                localpath=outFolder + '/' + name
                if outClass=='File':

                    with FTP(ftpdomain) as ftp:
                        ftp.login(ftpuser, ftppass)
                        # Remove the protocol "ftp://" and the domain
                        remotepath = re.sub("ftp://%s/" % ftpdomain,
                                            "/",
                                            url)
                        if(ftpLocal):
                            ftp.retrbinary("RETR %s" % remotepath, open(localpath, 'wb').write)
                        else:
                            try:
                                ftp.rename(remotepath,localpath)
                            except error_perm as err_perm:
                                print("Cannot rename file %s to %s" % (remotepath,localpath), file=sys.stderr)

        else:
            outClass=outputs[output]['class']
            name=outputs[output]['basename']
            url=outputs[output]['location']
            localpath=outFolder + '/' + name
            if outClass=='File':

                with FTP(ftpdomain) as ftp:
                    ftp.login(ftpuser, ftppass)
                    # Remove the protocol "ftp://" and the domain
                    remotepath = re.sub("ftp://%s/" % ftpdomain,
                                            "/",
                                            url)
                    if(ftpLocal):
                        ftp.retrbinary("RETR %s" % remotepath, open(localpath, 'wb').write)
                    else:
                        try:
                            ftp.rename(remotepath,localpath)
                        except error_perm as err_perm:
                            print("Cannot rename file %s to %s" % (remotepath,localpath), file=sys.stder)
    #for each task collect its info
    #clean up tesk jobs after keeping their logs
    for log in taskLogs:
        resources=log['resources']
        cpu+=float(resources['cpu_cores'])
        ram+=float(resources['ram_gb'])
        taskIds[log['id']]=log['name']
        taskSteps[i]=log['id']
        i+=1
    
    ram/=len(taskLogs);
    cpu/=len(taskLogs);

    updateStatus('Complete', jobid, start, stop, ram, int(cpu))

    # Logs
    kube_command='kubectl get pods -n ' + teskNamespace + ' --no-headers | tr -s " "'
    try:
        out=subprocess.check_output(kube_command,stderr=subprocess.STDOUT, shell=True)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(2)

    out=out.decode().split('\n')
    podLogs={}
    for line in out:
        pod=line.split(' ')
        if len(pod)==0:
            continue
        pod=pod[0].strip()

        if '-ex-' not in pod:
            continue

        podTokens=pod.split('-')
        task=podTokens[0] + '-' + podTokens[1]
        
        if task not in taskIds:
            continue

        kube_command='kubectl -n' + teskNamespace + ' logs ' + pod

        try:
            logs=subprocess.check_output(kube_command,stderr=subprocess.STDOUT, shell=True)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
            exit(3)
        podLogs[task]=logs.decode()

        subtasks=[task, task+'-ex-00', task + '-outputs-filer', task + '-inputs-filer']
        for subtask in subtasks:
            kube_command='kubectl -n' + teskNamespace + ' delete job ' + subtask
            try:
                logs=subprocess.check_output(kube_command,stderr=subprocess.STDOUT, shell=True)
            except subprocess.CalledProcessError as exc:
                print(exc.output)
                continue
    #write logs
    logfile=logPath + '/' + 'logs.txt'
    g=open(logfile,'w')    
    for i in range(1,len(taskSteps)):
        g.write('>>Step ' + str(i) + ': ' + taskIds[taskSteps[i]] + ' logs\n')
        g.write('------------------------\n')
        try:
          g.write(podLogs[taskSteps[i]] + '\n')
        except KeyError:
          g.write("NOLOG\n")
    g.close()
