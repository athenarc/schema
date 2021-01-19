#!/usr/bin/python3
import subprocess

def cleanJob(mounts,folder,jobName):
    command="kubectl get pods --no-headers -l job-name=" + jobName + " | tr -s ' '"
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        return 41

    podid=''
    out=out.split(' ')
    podid=out[0]


    command="kubectl logs " + podid + " 2>&1"

    try:
        logs=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        return 42

    logFile=folder + '/logs.txt'
    g=open(logFile,'w')
    g.write(logs)
    g.close()

    for mount in mounts:
        folder=mount[0]
        try:
            subprocess.call(['rm','-rf', folder])
        except:
            pass

    return 0

    

