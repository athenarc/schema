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
import workflowUploadFunctions as wuf
import sys
import subprocess
import re
import os.path
import json
import os

workName=sys.argv[1]
workVersion=sys.argv[2]
workflowPath=sys.argv[3]
workflowExtension=sys.argv[4]
user=sys.argv[5]
visibility=sys.argv[6]
description=sys.argv[7]
biotools=sys.argv[8]
doiFile=sys.argv[9]
covid19=sys.argv[10]
github_link=sys.argv[11]
instructions=sys.argv[12]

def quoteEnclose(string):
    return "'" + string + "'"

# Read configuration
configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()
# containerMount=config['workflowContainerMount']

workAllowedExt=set(['yaml','cwl'])
# Get the folder where the file was uploaded
tokens=workflowPath.split('/')
filename=tokens[-1]
folderTokens=tokens[0:-1]
folder='/'.join(folderTokens)

# If the whole workflow is not uploaded as a single cwl or yaml file,
# or if it is compressed, then uncompress, and find the main workflow file.
if workflowExtension not in workAllowedExt:
    if workflowExtension=='zip':
        subprocess.call(['unzip','-o',workflowPath, '-d', folder])
    elif workflowExtension=='gz':
        filenameTokens=filename.split('.')

        #Check if the file is .tar.gz or simple .gz and uncompress
        if (len(filenameTokens)>1):
            secondExtension=filenameTokens[1]
            if secondExtension=='tar':
                subprocess.call(['tar','xzf',workflowPath, '-C', folder])
            else:
                subprocess.call(['gzip','-d','-k','-f', workflowPath])
        else:
            subprocess.call(['gzip','-d','-k','-f', workflowPath])
    elif workflowExtension=='tar':
        subprocess.call(['tar','xvf',workflowPath, '-C', folder])

    # print(folder)
    workFile,retCode,content=wuf.getMainWorkflowFile(folder,workAllowedExt)
    if retCode!=0:
        exit(retCode)


else:
    workFile=workflowPath

print('Workfile: ' + workFile)
# workFile=workFile.replace(containerMount['local'],containerMount['wesContainer'])

try:
    content
except NameError:
    print("No content found in workflow");
    exit(3)

if 'inputs' not in content:
    exit(2)

wuf.workflowStore(workName,workVersion,workFile,user,visibility,
                description,biotools,doiFile,github_link,covid19,workflowPath,instructions)

inputs=content['inputs']
# print(content)
if isinstance(inputs,dict):
    exit_value=wuf.inputStoreDict(workName,workVersion,content['inputs'])
elif isinstance(inputs,list):
    exit_value=wuf.inputStoreList(workName,workVersion,content['inputs'])
else:
    exit_value=50
    
exit(exit_value)

# uf.imageStoreAndClassify(softName,softVersion, imageNew,script,user,visibility,
#     workingDir,imountPoint,omountPoint,description,cwlPath,biotools,doiFile,mpi,original,dockerHub,covid19)

# if 'inputs' not in cwlContent:
#     cwlContent['inputs']=[];

# exit_value=uf.inputStore(softName,softVersion, cwlContent['inputs'])
# exit(exit_value)

