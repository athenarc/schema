# import uuid
import json
import yaml
import os


configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

imagePullSecrets = config.get('imagePullSecrets', [])


def createFile(name,machineType,image,
		jobid,tmpFolder,workingDir,
		imountPoint,isystemMount,
		omountPoint,osystemMount,
		iomountPoint,iosystemMount,
		maxMem,maxCores,nfsIp):
	
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
		# command=['"' + x + '"' for x in command]
		# commandStr=','.join(command)
		# print(command)
		# print()
		# print(commandStr)
		# exit(0)
	# elif len(commands)==2:
	# 	commandStr=commands[0] + ' ; ' + commands[1]
	# else:
	# 	commandStr=commands[0] + ' ; ' + commands[1]
	# 	for i in range(2,len(commands)):
	# 		commandStr+= ' && ' + commands[i]

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

	manifest_data['spec']={'template':{'spec':{}}, 'backoffLimit':1}
	if len(volumes)!=0:
		manifest_data['spec']['template']['spec']['volumes']=volumes
	if imagePullSecrets:
		manifest_data['spec']['template']['spec']['imagePullSecrets'] = imagePullSecrets
	manifest_data['spec']['template']['spec']['containers']=containers
	# manifest_data['spec']['template']['spec']['nodeSelector']={'machine-type': machineType}
	manifest_data['spec']['template']['spec']['restartPolicy']='Never'
	# g.write('apiVersion: batch/v1\n')
	# g.write('kind: Job\n')
	# g.write('metadata:\n')
	# g.write(' name: ' + jobName + '\n')
	# g.write('spec:\n')
	# g.write(' ttlSecondsAfterFinished: 10\n')
	# g.write(' template:\n')
	# g.write('  spec:\n')
	# g.write('   volumes:\n')
	# g.write('   - name: ' + jobName + '-storage\n')
	# g.write('     hostPath:\n')
	# g.write('      path: ' + systemMount + '\n')
	# g.write('      type: Directory\n')
	# g.write('   containers:\n')
	# g.write('   - name: ' + jobName + '\n')
	# g.write('     resources:\n')
	# g.write('      limits:\n')
	# g.write('       memory: "' + maxMem + 'Gi"\n')
	# g.write('       cpu: "' + maxCores + 'm"\n')
	# g.write('     image: ' + image + '\n')
	# g.write('     workingDir: ' + workingDir + '/\n')
	# g.write('     command: [' + commandStr + ']\n')
	# # g.write('     command: ["/bin/sh","-c"]\n')
	# # g.write('     args: [' + commandStr + ']\n')
	# if (mountPoint!=''):
	# 	g.write('     volumeMounts:\n')
	# 	g.write('       - name: '+ jobName + '-storage\n')
	# 	g.write('         mountPath: '+ mountPoint + '\n')
	# g.write('   restartPolicy: Never\n')

	# g.write('   nodeSelector:\n')
	# g.write('    machine-type: '+ machineType +'\n')
	# g.write(' backoffLimit: 4\n')

	g=open(yamlName,'w')
	yaml.dump(manifest_data, g, default_flow_style=False)
	g.close()
	
	return yamlName
	
