import yaml
from notebook.auth import passwd


def createServerConfig(sid,cpu,mem,password,folder,image,mount,nfs, namespace, domain, platform):
    manifest=folder + '/' + sid + '-jupyter.yaml'
    appName=sid + '-jupyter'

    deployment={}
    volumes=[]
    containers=[]
    pod={}

    pod['replicas']=1
    pod['selector']={'matchLabels':{'app':appName}}
    pod['template']={'metadata':{'labels':{'app':appName}}}
    

    volumes=[]

    if nfs=='container':
        vname=sid + '-pvc'
        volume={'name': vname, 'persistentVolumeClaim':{'claimName':'schema-volume'}}
    else:
        vname=sid + '-nfs-storage'
        volume={'name': vname, 'nfs': {'server': nfs, 'path': mount}}
    volumes.append(volume)
    
    pod['template']['spec']={'volumes': volumes}

    container={}
    container['image']=image
    container['name']='jupyter-container'
    container['ports']=[{'containerPort':8888}]
    container['env']=[]
    container['env'].append({'name':'JUPYTER_ENABLE_LAB', 'value': 'yes'})

    container['resources']={'limits':{'cpu':str(cpu), 'memory':str(mem) + 'Gi'}, 'requests':{'cpu':str(cpu), 'memory':str(mem) + 'Gi'}}
    volumeMounts=[]
    if nfs=='container':
        vmount={'name': vname, 'mountPath': '/home/jovyan/work', 'subPath': mount.replace('/data/','')}
    else:
        vmount={'name': vname, 'mountPath': '/home/jovyan/work'}
    volumeMounts.append(vmount)
    container['volumeMounts']=volumeMounts

    hashed_password = passwd(passphrase=password, algorithm='sha256')

    command=['start-notebook.sh', "--NotebookApp.password='" + hashed_password + "'", "--NotebookApp.notebook_dir='/home/jovyan/work'"] 
    container['command']=command

    containers.append(container)

    pod['template']['spec']['containers']=containers
    if (int(mem) > 512) or (int(cpu)>=56):
        tolerations=[]
        tolerations.append({'key':'fat-node','operator':'Exists','effect':'NoExecute'})
        pod['template']['spec']['tolerations']=tolerations

    deployment['apiVersion']= 'apps/v1'
    deployment['kind']='Deployment'
    deployment['metadata']={'name': 'deployment-' + appName, 'labels':{'app':appName}, 'namespace':namespace}
    deployment['spec']=pod

    service={}
    service['apiVersion']= 'v1'
    service['kind']='Service'
    sname='service-' + appName
    service['metadata']={'name':sname, 'namespace':namespace}

    sspec={'type': 'ClusterIP', 'selector':{'app': appName}, 'ports':[{"protocol": 'TCP','port': 80, 'targetPort': 8888}]}
    service['spec']=sspec

    ingress={}
    if (platform=='kubernetes'):
        
        ingress['apiVersion']='networking.k8s.io/v1'
        ingress['kind']='Ingress'
        ingress['metadata']={'name': 'ingress-' + appName, 'annotations':{}, 'namespace':namespace}
        ingress['metadata']['annotations']['cert-manager.io/cluster-issuer']= 'letsencrypt-prod'
        ingress['metadata']['annotations']['kubernetes.io/ingress.class']= 'nginx'
        ingress['metadata']['annotations']['kubernetes.io/tls-acme']= "true"

        url=sid + '.' + domain

        ispec={'rules':[],'tls':[]}

        rule={'host':url, 'http':{'paths':[{'backend':{'service':{'name':sname, 'port':{'number':80} } }, 'path':'/', 'pathType':'Prefix' } ] } }
                                                

                                 

        tls={'hosts':[url], 'secretName': appName + '-ingress-secret'}

        ispec['rules'].append(rule)
        ispec['tls'].append(tls)

        ingress['spec']=ispec
    elif (platform=='openshift'):
        ingress['apiVersion']='route.openshift.io/v1'
        ingress['kind']='Route'
        ingress['metadata']={'name': 'ingress-' + appName, 'namespace':namespace}
        
        url=sid + '.' + domain

        tls={'insecureEdgeTerminationPolicy':'Redirect','termination':'edge'}
        to={'kind':'Service','name': sname,'weight':100}
        ingress['spec']={'host':url, 'tls':tls, 'to': to, 'wildcardPolicy':None}
        ingress['status']={'ingress':[]}
    else:
        pass

    g=open(manifest,'w')
    yaml.dump(deployment, g, default_flow_style=False)
    g.write('\n\n---\n\n')
    yaml.dump(service, g, default_flow_style=False)
    g.write('\n\n---\n\n')
    yaml.dump(ingress, g, default_flow_style=False)
    g.close()
    
    return manifest,url




