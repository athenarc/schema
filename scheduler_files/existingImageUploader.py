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

softName=sys.argv[1]
softVersion=sys.argv[2]
image=sys.argv[3]
cwlPath=sys.argv[4]
user=sys.argv[5]
visibility=sys.argv[6]
imountPoint=sys.argv[7]
omountPoint=sys.argv[8]
description=sys.argv[9]
biotools=sys.argv[10]
doiFile=sys.argv[11]
workingDir=sys.argv[12]
original=sys.argv[13]
docker_or_local=sys.argv[14]
covid19=sys.argv[15]
instructions=sys.argv[16]
gpu=sys.argv[17]

def quoteEnclose(string):
    return "'" + string + "'"

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()


if cwlPath!='':
    cwlContent=uf.cwlReadFile(cwlPath)
    # cImageFull=uf.cwlReturnDockerImage(cwlContent)
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

uf.imageStore(softName,softVersion, image,script,user,visibility,
    workingDir,imountPoint,omountPoint,description,cwlPath,biotools,doiFile,original,docker_or_local,covid19,instructions,gpu)

if 'inputs' not in cwlContent:
    cwlContent['inputs']=[];

if isinstance(cwlContent['inputs'],dict):
    exit_value=uf.inputStoreDict(softName,softVersion, cwlContent['inputs'])
elif isinstance(cwlContent['inputs'],list):
    exit_value=uf.inputStoreList(softName,softVersion, cwlContent['inputs'])
else:
    exit_value=100
exit(exit_value)

