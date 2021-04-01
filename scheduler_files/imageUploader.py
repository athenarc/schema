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
import uploadFunctions as uf
import sys
import subprocess
import re
import os.path
import json
import os
import uuid
import psycopg2 as psg
import time
from dockertarpusher import Registry

softName=sys.argv[1]
softVersion=sys.argv[2]
imagePath=sys.argv[3]
imageExtension=sys.argv[4]
cwlPath=sys.argv[5]
user=sys.argv[6]
visibility=sys.argv[7]
imountPoint=sys.argv[8]
omountPoint=sys.argv[9]
description=sys.argv[10]
biotools=sys.argv[11]
doiFile=sys.argv[12]
mpi=sys.argv[13]
covid19=sys.argv[14]
instructions=sys.argv[15]

def quoteEnclose(string):
    return "'" + string + "'"

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()

uImageFull=''
cImageFull=''
script=''
registry=config['registry'].strip('/')
regAuth=config['registryAuth']

if cwlPath!='':
    cwlContent=uf.cwlReadFile(cwlPath)
    # print(cwlContent)
    cImageFull=uf.cwlReturnDockerImage(cwlContent)
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

uimage=''
if imagePath!='':
    reg=Registry('https://'+ registry, imagePath, regAuth['username'],regAuth['password'])
    manifestFile = reg.getManifest()[0]
    uimage=manifestFile['RepoTags'][0]
    uImageFull=registry + '/' + uimage

#if an image is not provied by cwl or direct upload
if (uImageFull=='') and (cImageFull==''):
    exit(3)
#if both images are provided
elif (uImageFull!='') and (cImageFull!=''):
    #if the user has already uploaded an image which is different than the cwl definition
        #delete previous image and exit
    if uImageFull!=cImageFull:
        exit(4)
    else:
        imageFull=uImageFull
        dockerHub='f';
#if only the cwl image is provided, pull it locally
elif (uImageFull=='') and (cImageFull!=''):
    imageFull=cImageFull
    dockerHub='t'
    
#if image not provided by cwl but has been uploaded
else:
    dockerHub='f'
    imageFull=uImageFull
    
# imageName=softName.lower()
# imageVersion=softVersion.lower()

workingDir='/'


if (dockerHub=='t'):
    original=imageFull
else:
    original=uimage

if (dockerHub=='f'):
    sql="SELECT COUNT(*) FROM operation_locks where operation='image_delete'"
    cur.execute(sql)
    results=cur.fetchone()
    opCount=results[0]
    while opCount>0:
        time.sleep(5)
        sql="SELECT COUNT(*) FROM operation_locks where operation='image_delete'"
        cur.execute(sql)
        results=cur.fetchone()
        opCount=results[0]

    uniqid=uuid.uuid4()
    uniqid=str(uniqid)
    sql="INSERT INTO operation_locks(id,operation) VALUES ('" + uniqid + "','image_upload')"
    cur.execute(sql)
    conn.commit()

    command=['docker-tar-push','https://' + registry, imagePath, regAuth['username'],regAuth['password']]
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT)
        print(out)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(8)


    sql="DELETE FROM operation_locks WHERE id='" + uniqid + "'"
    cur.execute(sql)
    conn.commit()

uf.imageStore(softName,softVersion, imageFull,script,user,visibility,
    workingDir,imountPoint,omountPoint,description,cwlPath,biotools,doiFile,mpi,original,dockerHub,covid19,instructions)

if 'inputs' not in cwlContent:
    cwlContent['inputs']=[];

exit_value=uf.inputStore(softName,softVersion, cwlContent['inputs'])
exit(exit_value)

