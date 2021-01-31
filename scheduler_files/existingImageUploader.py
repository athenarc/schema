#!/usr/bin/python3
import uploadFunctions as uf
import sys
import subprocess
import re
import os.path
import json
import os

softName=sys.argv[1]
softVersion=sys.argv[2]
image=sys.argv[3]
cwlPath=sys.argv[4]
user=sys.argv[5]
visibility=sys.argv[6]
imountPoint=sys.argv[7]
omountPoint=sys.argv[8]
commandRetr=sys.argv[9]
description=sys.argv[10]
biotools=sys.argv[11]
doiFile=sys.argv[12]
mpi=sys.argv[13]
workingDir=sys.argv[14]
original=sys.argv[15]
docker_or_local=sys.argv[16]
covid19=sys.argv[17]

def quoteEnclose(string):
    return "'" + string + "'"

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()


if cwlPath!='':
    cwlContent=uf.cwlReadFile(cwlPath)
    # cImageFull=uf.cwlReturnDockerImage(cwlContent)
    if (commandRetr=='cwl'):
        if ('baseCommand' not in cwlContent):
            exit(12)
        else:
            if isinstance(cwlContent['baseCommand'],list):
                script=' '.join(cwlContent['baseCommand'])
            else:
                script=cwlContent['baseCommand']
else:
    exit(11)

if (imountPoint=='/') or (omountPoint=='/'):
    exit(13)


if workingDir=='':
    workingDir='/'

uf.imageStoreAndClassify(softName,softVersion, image,script,user,visibility,
    workingDir,imountPoint,omountPoint,description,cwlPath,biotools,doiFile,mpi,original,docker_or_local,covid19)

if 'inputs' not in cwlContent:
    cwlContent['inputs']=[];

exit_value=uf.inputStore(softName,softVersion, cwlContent['inputs'])
exit(exit_value)

