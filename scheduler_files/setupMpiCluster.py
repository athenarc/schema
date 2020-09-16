#!/usr/bin/python
import mpiConfigFileCreator as cf
import sys
import subprocess
import time
import json

name=sys.argv[1]
image=sys.argv[2]
jobid=sys.argv[3]
tmpfolder=sys.argv[4]
imountpoint=sys.argv[5]
isystemMount=sys.argv[6]
omountpoint=sys.argv[7]
osystemMount=sys.argv[8]
iomountpoint=sys.argv[9]
iosystemMount=sys.argv[10]
maxMem=sys.argv[11]
maxCores=sys.argv[12]
pernode=sys.argv[13]
nfsIp=sys.argv[14]

filename=cf.createFile(name,image,jobid,tmpfolder,imountpoint,isystemMount,omountpoint,
    osystemMount,iomountpoint,iosystemMount,maxMem,maxCores,pernode,nfsIp)

code=subprocess.call(['kubectl', 'create', '-n','mpi-cluster','-f',filename])

if (code!=0):
    exit(1)

command='kubectl get pods -n mpi-cluster --no-headers'

while True:
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(2)
    pods=out.split('\n')
    ready=True
    # print(pods)
    for pod in pods:
        if pod=='':
            continue
        pod=pod.split()
        status=pod[2]
        # print(pod[0],status)
        if status!='Running':
            ready=False
            break
    if not ready:
        time.sleep(2)
    else:
        break

