#!/usr/bin/python3
import sys
import subprocess
import psycopg2 as psg
import time
import os
import json

def podStatus():

    command='kubectl get pods -n mpi-cluster --no-headers'

    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(2)
    
    if 'No resources' in out:
        return 'down'

    pods=out.split('\n')
    for pod in pods:
        if pod=='':
            continue
        pod=pod.split()
        status=pod[2]
        if status=='Error':
            return 'error'
        elif status=='Terminating':
            return 'terminating'
    return 'running'

def commandRunning():
    command='kubectl exec -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c "ps aux | grep mpiexec"'

    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(2)
    lines=out.split('\n')
    lines=[x for x in lines if x!=''];

    if len(lines)>2:
        return True
    else:
        return False


yamlFolder=sys.argv[1]
yamlFile=sys.argv[2]
jobid=sys.argv[3]

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

while True:
    status=podStatus()

    if (status=='down') or (status=='terminating'):
        conn=conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
        cur=conn.cursor()
        sql="UPDATE run_history SET stop=NOW(), status='Canceled' where jobid='" + jobid + "'" 
        cur.execute(sql)
        conn.commit()
        conn.close()
        # command='kubectl delete -f ' + yamlFile + '  -n mpi-cluster'
        # try:
        #     out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
        # except subprocess.CalledProcessError as exc:
        #     print(exc.output)
        #     exit(2)

        break
    elif (status=='error'):
        conn=conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
        cur=conn.cursor()
        sql="UPDATE run_history SET stop=NOW(), status='Error' where jobid='" + jobid + "'" 
        cur.execute(sql)
        conn.commit()
        conn.close()
        command='kubectl delete -f ' + yamlFile + '-n mpi-cluster'
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
            exit(2)

        break
    elif (status=='running'):

        commStatus=commandRunning()
        # print(commStatus)

        if commStatus==True:
            time.sleep(4)
        else:
            conn=conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
            cur=conn.cursor()
            sql="UPDATE run_history SET stop=NOW(), status='Complete' where jobid='" + jobid + "'" 
            cur.execute(sql)
            conn.commit()
            conn.close()

            # Dump logs
            logfile=yamlFolder + '/logs.txt'
            command='kubectl exec -n mpi-cluster mpi-master -c mpi-master -- /bin/sh -c "cat /logs.txt"'
            try:
                out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
            except subprocess.CalledProcessError as exc:
                print(exc.output)
                exit(2)
            g=open(logfile,'w')
            g.write(out)
            g.close()
            subprocess.call(['chmod','777',logfile])

            command='kubectl delete -f ' + yamlFile + ' -n mpi-cluster'
            try:
                out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
            except subprocess.CalledProcessError as exc:
                print(exc.output)
                exit(2)
            break


