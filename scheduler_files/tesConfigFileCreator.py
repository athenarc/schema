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
import json
import yaml
import os


configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

imagePullSecrets = config.get('imagePullSecrets', [])


def createFile(data,mounts,folder,jobid,nfsIp,maxCores,maxMem):
    
    jobName='remote-' + jobid

    yamlPath=folder + '/' + jobName + '.yaml'

    try:
        executors=data['executors']
    except:
        return 11,'',''
    



    volumes=[]
    volumeMounts=[]
    i=0
    for mount in mounts:
        local=mount[0]
        mpath=mount[1]
        volume={'name': jobName + '-nfs-storage-' + str(i)}
        volume['nfs']={'server': nfsIp, 'path': local}
        volumeMount={'name': volume['name'], 'mountPath': mpath}
        volumes.append(volume)
        volumeMounts.append(volumeMount)
        i+=1

    
    if len(executors)==0:
        return 12,''

    initExecutors=[]
    if len(executors)>1:
        initExecutors=executors[:-1]
        executors=executors[-1:]
    
    initContainers=[]
    containers=[]
    #
    # Start counting all of the initContainers,
    # as well as the last container, to add unique numbers
    #
    i=0
    for executor in initExecutors:
        try:
            command=executor['command']
        except:
            return 13,'',''
        try:
            command=executor['image']
        except:
            return 14,'',''
        try:
            envs=executor['env']
        except KeyError:
            envs=[]
        try:
            workdir=executor['workdir']
        except KeyError:
            workdir=''
    
        container={'name':jobName  + '-' + str(i), 'resources':{}, 'image':executor['image']}
        container['resources']={'limits': {'memory': maxMem + 'Gi', 'cpu':maxCores + 'm'}}
        if workdir!='':
            container['workingDir']=executor['workdir']
        container['env']=[]
        for env in envs:
            container['env'].append({'name':env,'value':envs[env]})
        container['command']=executor['command']
        container['volumeMounts']=volumeMounts
        initContainers.append(container)
        i+=1
    for executor in executors:
        try:
            command=executor['command']
        except:
            return 13,'',''
        try:
            command=executor['image']
        except:
            return 14,'',''
        try:
            envs=executor['env']
        except KeyError:
            envs=[]
        try:
            workdir=executor['workdir']
        except KeyError:
            workdir=''
        container={'name':jobName  + '-' + str(i), 'resources':{}, 'image':executor['image']}
        container['resources']={'limits': {'memory': maxMem + 'Gi', 'cpu':maxCores + 'm'}}
        if workdir!='':
            container['workingDir']=executor['workdir']
        container['env']=[]
        for env in envs:
            container['env'].append({'name':env,'value':envs[env]})
        container['command']=executor['command']
        container['volumeMounts']=volumeMounts
        containers.append(container)
        i+=1


    
    manifest_data={}
    manifest_data['apiVersion']='batch/v1'
    manifest_data['kind']='Job'
    manifest_data['metadata']={'name': jobName}

    manifest_data['spec']={'template':{'spec':{}}, 'backoffLimit':1}
    if len(volumes)!=0:
        manifest_data['spec']['template']['spec']['volumes']=volumes
    if imagePullSecrets:
        manifest_data['spec']['template']['spec']['imagePullSecrets'] = imagePullSecrets
    manifest_data['spec']['template']['spec']['containers']=containers
    manifest_data['spec']['template']['spec']['initContainers']=initContainers
    manifest_data['spec']['template']['spec']['restartPolicy']='Never'
    

    g=open(yamlPath,'w')
    yaml.dump(manifest_data, g, default_flow_style=False)
    g.close()
    
    return 0,yamlPath,jobName
    
