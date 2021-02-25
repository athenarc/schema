#!/usr/bin/python3

import sys
import yaml
import subprocess
import time
import os.path
import json

jobConfFileName=sys.argv[1];
jobConfFile=open(jobConfFileName,'r')
jobConf=json.load(jobConfFile);
jobConfFile.close()

def createFile(jobName,jobConf,command):
    
    configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
    configFile=open(configFileName,'r')
    config=json.load(configFile)
    configFile.close()

    imagePullSecrets = config.get('imagePullSecrets', [])
    image=jobConf['image']
    workingDir=jobConf['workdir']
    iosystemMount=jobConf['systemMount']
    iomountPoint=jobConf['mountpoint']
    nfsIp=jobConf['nfs']
    maxMem='125'
    maxCores='30000'
    yamlName= jobConf['folder'] + '/manifests/' + jobName + '.yaml'

    volumes=[]
    mounts=[]
    if iomountPoint!='':
        # volume={'name': jobName + '-storage'}
        # volume['hostPath']={'path': iosystemMount, 'type':'Directory'}
        # mount={'name': volume['name'], 'mountPath': iomountPoint}

        
        volume={'name': jobName + '-nfs-storage'}
        volume['nfs']={'server': nfsIp, 'path': iosystemMount}
        mount={'name': volume['name'], 'mountPath': iomountPoint}

        volumes.append(volume)
        mounts.append(mount)
        
    else:
        if imountPoint!='':
            # volume={'name': jobName + '-input-storage'}
            # volume['hostPath']={'path': isystemMount, 'type':'Directory'}
            # mount={'name': volume['name'], 'mountPath': imountPoint}

            volume={'name': jobName + '-nfs-input-storage'}
            volume['nfs']={'server': nfsIp, 'path': isystemMount}
            mount={'name': volume['name'], 'mountPath': imountPoint}

            volumes.append(volume)
            mounts.append(mount)
        if omountPoint!='':
            # volume={'name': jobName + '-output-storage'}
            # volume['hostPath']={'path': osystemMount, 'type':'Directory'}
            # mount={'name': volume['name'], 'mountPath': omountPoint}

            volume={'name': jobName + '-nfs-output-storage'}
            volume['nfs']={'server': nfsIp, 'path': osystemMount}
            mount={'name': volume['name'], 'mountPath': omountPoint}

            volumes.append(volume)
            mounts.append(mount)
    # print(volumes)
    # exit(0)
    containers=[]
    container={'name':jobName, 'resources':{}, 'image':image}
    container['resources']={'limits': {'memory': maxMem + 'Gi', 'cpu':maxCores + 'm'}}
    container['workingDir']=workingDir
    container['command']=command
    containers.append(container)

    if len(mounts)!=0:
        container['volumeMounts']=mounts

    
    manifest_data={}
    manifest_data['apiVersion']='batch/v1'
    manifest_data['kind']='Job'
    manifest_data['metadata']={'name': jobName}

    manifest_data['spec']={'template':{'spec':{}}, 'backoffLimit':0}
    if len(volumes)!=0:
        manifest_data['spec']['template']['spec']['volumes']=volumes
    if imagePullSecrets:
        manifest_data['spec']['template']['spec']['imagePullSecrets'] = [{'name':imagePullSecrets}]
    manifest_data['spec']['template']['spec']['containers']=containers
    # manifest_data['spec']['template']['spec']['nodeSelector']={'machine-type': machineType}
    manifest_data['spec']['template']['spec']['restartPolicy']='Never'

    tolerations=[]
    tolerations.append({'key':'fat-node','operator':'Exists','effect':'NoExecute'})
    manifest_data['spec']['template']['spec']['tolerations']=tolerations
    manifest_data['spec']['template']['spec']['nodeSelector']={'node-type':'fat-node'}
    
    g=open(yamlName,'w')
    yaml.dump(manifest_data, g, default_flow_style=False)
    g.close()
    
    return yamlName


i=0
for jobCommand in jobConf['commands']:
    
    jobName=jobConf['name'].lower() + '-' + jobConf['id'] + '-' + str(i)

    yamlFile=createFile(jobName,jobConf,jobCommand)

    podid='No'
    #run file
    command=['kubectl', 'create', '-f', yamlFile]
    subprocess.call(command)
    #find podid and count memory
    while(podid=='No'):
        command="kubectl get pods --no-headers -l job-name=" + jobName + " | tr -s ' '"
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            print(exc.output)

        podid=''
        out=out.split(' ')
        podid=out[0]
    status=out[2]
    status_code=0
    cpu=0
    memory=0
    while (status!='Completed') and (status!='Error') and (status!='ErrImagePullBackOff') and (status!="ContainerCannotRun") and (status!="RunContainerError") and (status!="OOMKilled"):
        
        code=0
        command="kubectl top pod --no-headers " + podid 
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            code=exc.returncode            

        if code==0:
            command='echo  "'+ out + "\" | tr -s ' ' " 
            try:
                out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
            except subprocess.CalledProcessError as exc:
                code=exc.returncode
            tokens=out.split(' ')
            topmem=float(tokens[2][:-2])

            memmeasure=tokens[2][-2:]

            if memmeasure=='Mi':
                topmem/=1024.0
            elif memmeasure=='Ki':
                topmem/=(1024.0*1024)
            elif memmeasure=='Gi':
                pass
            
            if memory<topmem:
                memory=topmem
        
        time.sleep(1)

        command="kubectl get pod --no-headers " + podid + " | tr -s ' '"
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
        except subprocess.CalledProcessError as exc:
            print(exc.output)
        #Job was canceled
        if ("No" in out) and (memory!=0):
            status='Canceled'
            break
        #If it is running, get the status
        out=out.strip().split('\n')
        if len(out)>1:
            for line in out:
                line=line.split(' ')
                status=line[2]
                print(out)
                if status=='OOMKilled':
                    break
        else:
            out=out[0]
            out=out.split(' ')
            status=out[2]
        # print(command)
        # print(out,memory)

    command=['kubectl', 'delete', '-f', yamlFile]
    subprocess.call(command)
    fields=jobCommand[1:]
    included=jobConf['included']
    if memory>0:
        record=[]
        fileSizes=jobConf['fileSizes']
        for index in included:
            item=fields[index]
            try:
                fsize=fileSizes[item]
                record.append(item)
                record.append(fsize)
            except:
                record.append(item)


        record.append(str(memory))
        gname=jobConf['folder'] + 'final-' + jobConf['id'] + '.txt'

        g=open(gname,'a')
        g.write('|'.join(record) + '\n')
        g.close()

    i+=1

                        


