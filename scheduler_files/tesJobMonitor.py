#!/usr/bin/python3
import os
import sys
import subprocess
import time
import psycopg2 as psg
import json
from datetime import datetime

def monitorJob(jobName,jobid):

    configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
    configFile=open(configFileName,'r')
    config=json.load(configFile)
    configFile.close()

    db=config['database']
    host=db['host']
    dbuser=db['username']
    passwd=db['password']
    dbname=db['database']


    command="kubectl get pods --no-headers -l job-name=" + jobName + " | tr -s ' '"
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        print(exc.output)

    podid=''
    out=out.split(' ')
    podid=out[0]
    status=out[2]
    status_code=0
    cpu=0
    memory=0
    while (status!='Completed') and (status!='Error') and (status!='ErrImagePullBackOff') and (status!="ContainerCannotRun") and (status!="RunContainerError"):
        
        code=0
        command="kubectl top pod --no-headers " + podid 
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            code=exc.returncode            

        if code==0:
            command='echo  "'+ out + "\" | tr -s ' ' " 
            try:
                out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
            except subprocess.CalledProcessError as exc:
                code=exc.returncode
            tokens=out.split(' ')
            topmem=float(tokens[2][:-2])

            memmeasure=tokens[2][-2:]

            if memmeasure=='Mi':
                topmem/=1024.0
            elif memmeasure=='Ki':
                topmem/=(1024.0*1024)
            
            if memory<topmem:
                memory=topmem
            topcpu=int(tokens[1][:-1])
            if cpu<topcpu:
                cpu=topcpu
        
        time.sleep(1)

        command="kubectl get pod --no-headers " + podid + " | tr -s ' '"
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            print(exc.output)
        #Job was canceled
        if "No" in out:
            status='Canceled'
            break
        #If it is running, get the status
        out=out.split(' ')
        status=out[2]
    
    if status!='Canceled':
    #Get start and end times
        command="kubectl get job " + jobName + " -o=jsonpath='{.status.completionTime}'"

        try:
            endOut=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            print(exc.output)
        end=endOut.replace('T',' ').replace('Z',' ').strip()

        command="kubectl get job " + jobName + " -o=jsonpath='{.status.startTime}'"
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            print(exc.output)
        start=out.replace('T',' ').replace('Z',' ').strip()

        if status=='Completed':
            status='Complete'
        

    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()
    if status=='Canceled':
        query="UPDATE run_history SET stop='NOW()', remote_status_code=-10, status='" + status +"' WHERE jobid='" + jobid + "'"
        status_code=-1
    elif status=='Complete':
        query="UPDATE run_history SET ram=" + str(memory) + ", cpu=" + str(cpu) + ", start='" + start +"', stop='" + str(end) + "', status='" + status +"' WHERE jobid='" + jobid + "'"
        status_code=0
    else:
        query="UPDATE run_history SET stop='NOW()', status='Error', remote_status_code=-9 WHERE jobid='" + jobid + "'"
        status_code=-2
    # print(query)
    cur.execute(query)
    conn.commit()

    conn.close()


    return status_code
