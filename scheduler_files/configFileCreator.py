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
namespaces=config.get('namespaces',None)

jobNamespace=None
if namespaces is not None:
    jobNamespace=namespaces.get('jobs',None)


def createFile(name,machineType,image,
        jobid,tmpFolder,workingDir,
        imountPoint,isystemMount,
        omountPoint,osystemMount,
        iomountPoint,iosystemMount,
        maxMem,maxCores,nfsIp,sharedFolder,gpu):
    
    if os.path.exists('/data/containerized'):
        inContainer=True
    else:
        inContainer=False

    jobName=name.lower().replace(' ','-').replace('\t','-') + '-' + jobid

    yamlName=tmpFolder + '/' + jobName + '.yaml'
    commandFile=tmpFolder + '/' + 'commands.txt'

    commands=[]
    f=open(commandFile,'r')
    for line in f:
        command=line.strip()
        if command!='':
            commands.append(command)
    f.close()

    if len(commands)==1:
        command=commands[0].split()
    

    volumes=[]
    mounts=[]
    if not inContainer:
        if len(sharedFolder)>0:
            volume={'name': jobName + '-nfs-shared-storage'}
            volume['nfs']={'server': nfsIp, 'path': sharedFolder}
            mount={'name': volume['name'], 'mountPath': '/shared'}

            volumes.append(volume)
            mounts.append(mount)
        if iomountPoint!='':
            volume={'name': jobName + '-nfs-storage'}
            volume['nfs']={'server': nfsIp, 'path': iosystemMount}
            mount={'name': volume['name'], 'mountPath': iomountPoint}

            volumes.append(volume)
            mounts.append(mount)
            
        else:
            if imountPoint!='':
                volume={'name': jobName + '-nfs-input-storage'}
                volume['nfs']={'server': nfsIp, 'path': isystemMount}
                mount={'name': volume['name'], 'mountPath': imountPoint}

                volumes.append(volume)
                mounts.append(mount)
            
            if omountPoint!='':
                volume={'name': jobName + '-nfs-output-storage'}
                volume['nfs']={'server': nfsIp, 'path': osystemMount}
                mount={'name': volume['name'], 'mountPath': omountPoint}

                volumes.append(volume)
                mounts.append(mount)
    else:
        volume={'name': jobName + '-volume', 'persistentVolumeClaim':{'claimName':'schema-data-volume'}}
        volumes.append(volume)

        if len(sharedFolder)>0:
            mount={'name': volume['name'], 'mountPath': '/shared', 'subPath': sharedFolder.replace('/data/','')}
            mounts.append(mount)


        if iomountPoint!='':
            mount={'name': volume['name'], 'mountPath': iomountPoint, 'subPath': iosystemMount.replace('/data/','')}
            mounts.append(mount)
            
        else:
            if imountPoint!='':
                mount={'name': volume['name'], 'mountPath': imountPoint, 'subPath': isystemMount.replace('/data/','')}
                mounts.append(mount)
            
            if omountPoint!='':
                mount={'name': volume['name'], 'mountPath': omountPoint, 'subPath': osystemMount.replace('/data/','')}
                mounts.append(mount)
    # print(volumes)
    # exit(0)
    containers=[]
    container={'name':jobName, 'resources':{}, 'image':image}
    container['resources']={'limits': {'memory': maxMem + 'Gi', 'cpu':maxCores + 'm'}}
    if (gpu=='1'):
        container['resources']['limits']['nvidia.com/gpu']='1'
    
    container['workingDir']=workingDir
    container['command']=command
    containers.append(container)

    if len(mounts)!=0:
        container['volumeMounts']=mounts

    
    manifest_data={}
    manifest_data['apiVersion']='batch/v1'
    manifest_data['kind']='Job'
    manifest_data['metadata']={'name': jobName}
    if jobNamespace is not None:
        manifest_data['metadata']['namespace']=jobNamespace

    manifest_data['spec']={'template':{'spec':{}}, 'backoffLimit':0}
    if len(volumes)!=0:
        manifest_data['spec']['template']['spec']['volumes']=volumes
    if imagePullSecrets:
        manifest_data['spec']['template']['spec']['imagePullSecrets'] = imagePullSecrets
    manifest_data['spec']['template']['spec']['containers']=containers
    manifest_data['spec']['template']['spec']['restartPolicy']='Never'

    #if memory is large, add tolerations:
    if (int(maxMem) > 512) or (int(maxCores)>=56):
        tolerations=[]
        tolerations.append({'key':'fat-node','operator':'Exists','effect':'NoExecute'})
        manifest_data['spec']['template']['spec']['tolerations']=tolerations
    if machineType!='converged-node':
        manifest_data['spec']['template']['spec']['nodeSelector']={'node-type':machineType}
    
    g=open(yamlName,'w')
    yaml.dump(manifest_data, g, default_flow_style=False)
    g.close()
    
    return yamlName