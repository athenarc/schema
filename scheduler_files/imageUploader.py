#!/usr/bin/env python
import uploadFunctions as uf
import sys
import subprocess
import re
import os.path
import json
import os

softName=sys.argv[1]
softVersion=sys.argv[2]
imagePath=sys.argv[3]
imageExtension=sys.argv[4]
cwlPath=sys.argv[5]
user=sys.argv[6]
visibility=sys.argv[7]
imountPoint=sys.argv[8]
omountPoint=sys.argv[9]
commandRetr=sys.argv[10]
description=sys.argv[11]
biotools=sys.argv[12]
doiFile=sys.argv[13]
mpi=sys.argv[14]
covid19=sys.argv[15]
instructions=sys.argv[16]

def quoteEnclose(string):
    return "'" + string + "'"

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

uImageFull=''
cImageFull=''
script=''
registry=config['registry']

if cwlPath!='':
    cwlContent=uf.cwlReadFile(cwlPath)
    cImageFull=uf.cwlReturnDockerImage(cwlContent)
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

if imagePath!='':
    if imageExtension=='gz':
        # command=['gzip', '-d', imagePath, '|', 'docker', 'load'];
        command='gzip -cd ' + quoteEnclose(imagePath) + ' | docker load'
    else:
        # command=['docker', 'load', '-i', imagePath]
        command='docker load -i ' + quoteEnclose(imagePath)
    # print(command)
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT, shell=True)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(2)

    # print(out)
    patt=re.compile('Loaded image: (.+:.+)')
    uImageFull=patt.match(out).groups()[0]
    # uImageSplit=uImageFull.split(':')
    # uImageName=uImageSplit[0]
    # uImageVersion=uImageSplit[1]



# print(uImageFull,cImageFull)
# exit(0)
#if an image is not provied by cwl or direct upload
if (uImageFull=='') and (cImageFull==''):
    exit(3)
#if both images are provided
elif (uImageFull!='') and (cImageFull!=''):
    #if the user has already uploaded an image which is different than the cwl definition
        #delete previous image and exit
    if uImageFull!=cImageFull:
        command=['docker','image','rm',uImageFull]
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
        exit(4)
    else:
        # imageName=uImageName
        imageFull=uImageFull
        dockerHub='f';
        # imageVersion=uImageVersion
#if only the cwl image is provided, pull it locally
elif (uImageFull=='') and (cImageFull!=''):
    imageFull=cImageFull
    dockerHub='t'
    # imageSplit=imageFull.split(':')
    # imageName=imageSplit[0]
    # if len(imageSplit)<2:
    #     imageVersion=softVersion
    # else:
    #     imageVersion=imageSplit[1]
    command=['docker','pull',imageFull]
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT)
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        exit(5)
#if image not provided by cwl but has been uploaded
else:
    dockerHub='f'
    imageFull=uImageFull
    
imageName=softName.lower()
imageVersion=softVersion.lower()
# print('/data/registry/docker/registry/v2/repositories/' + imageName + ':' + softVersion +'/_manifests/tags/latest')
# if os.path.exists('/data/registry/docker/registry/v2/repositories/' + imageName + '-' + imageVersion +'/_manifests/tags/latest'):
# # if os.path.exists('/data/docker/registrytest/docker/registry/v2/repositories/' + imageName + '-' + softVersion +'/_manifests/tags/latest'):
#     command=['docker','image','rm',imageFull]
#     try:
#         out=subprocess.check_output(command,stderr=subprocess.STDOUT)
#     except subprocess.CalledProcessError as exc:
#         print(exc.output)
#     exit(6)


#read image information regarding working directory and container config
command=['docker','image','inspect',imageFull]
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)
    exit(9)
# print (json.loads(out)[0]['ContainerConfig'])
# exit(9)
try:
    decoded=json.loads(out)[0]
    containerConfig=decoded['ContainerConfig']
    config=decoded['Config']
    if 'WorkingDir' in config:
        workingDir=config['WorkingDir']
    else:
        workingDir='/'
except:
    workingDir='/'

# if the user instructed the interface to get the base command from the image
if (commandRetr=='image'):
    if 'Cmd' in config:
        script=' '.join(config['Cmd'])
    else:
        exit(15)

# first save the name of the local image as original
# and then add the registry in the string
# and replace invalid characters

imageNew= imageName + '-' + imageVersion + ':latest';


if (dockerHub=='t'):
    original=imageFull
else:
    original=imageNew

imageNew=registry + imageNew
imageNew=imageNew.replace(' ','_')
imageNew=imageNew.replace('\t','_')



command=['docker','image','tag',imageFull,imageNew]
# print command
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)
    exit(7)

command=['docker','push',imageNew]
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)
    exit(8)

command=['docker','image','rm',imageNew]
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)
    exit(9)
command=['docker','image','rm',imageFull]
try:
    out=subprocess.check_output(command,stderr=subprocess.STDOUT)
except subprocess.CalledProcessError as exc:
    print(exc.output)
    exit(10)

uf.imageStoreAndClassify(softName,softVersion, imageNew,script,user,visibility,
    workingDir,imountPoint,omountPoint,description,cwlPath,biotools,doiFile,mpi,original,dockerHub,covid19,instructions)

if 'inputs' not in cwlContent:
    cwlContent['inputs']=[];

exit_value=uf.inputStore(softName,softVersion, cwlContent['inputs'])
exit(exit_value)

