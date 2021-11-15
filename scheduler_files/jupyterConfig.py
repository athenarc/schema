import yaml
from notebook.auth import passwd


def createServerConfig(sid,cpu,mem,password,folder,image,mount,nfs, namespace):
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
        volume={'name': vname, 'persistentVolumeClaim':{'claimName':'schema-data-volume'}}
    else:
        vname=sid + '-nfs-storage'
        volume={'name': vname, 'nfs': {'server': nfs, 'path': mount}}
    volumes.append(volume)
    
    pod['template']['spec']={'volumes': volumes}

    container={}
    container['image']=image
    container['name']='jupyter-container'
    container['ports']=[{'containerPort':8888}]
    cpu*=1000

    container['resources']={'limits':{'cpu':str(cpu)+'m', 'memory':str(mem) + 'G'}}
    volumeMounts=[]
    vmount={'name': vname, 'mountPath': '/home/jovyan/work'}
    volumeMounts.append(vmount)
    container['volumeMounts']=volumeMounts

    hashed_password = passwd(passphrase=password, algorithm='sha256')

    command=['start-notebook.sh', "--NotebookApp.password='" + hashed_password + "'", "--NotebookApp.notebook_dir='/home/jovyan/work'"] 
    container['command']=command

    containers.append(container)

    pod['template']['spec']['containers']=containers

    deployment['apiVersion']= 'apps/v1'
    deployment['kind']='Deployment'
    deployment['metadata']={'name': 'deployment-' + appName, 'labels':{'app':appName}, 'namespace':namespace}
    deployment['spec']=pod

    service={}
    service['apiVersion']= 'v1'
    service['kind']='Service'
    sname='service-' + appName
    service['metadata']={'name':sname, 'namespace':'jupyter'}

    sspec={'type': 'ClusterIP', 'selector':{'app': appName}, 'ports':[{"protocol": 'TCP','port': 80, 'targetPort': 8888}]}
    service['spec']=sspec

    ingress={}
    ingress['apiVersion']='networking.k8s.io/v1'
    ingress['kind']='Ingress'
    ingress['metadata']={'name': 'ingress-' + appName, 'annotations':{}, 'namespace':'jupyter'}
    ingress['metadata']['annotations']['cert-manager.io/cluster-issuer']= 'letsencrypt-prod'
    ingress['metadata']['annotations']['kubernetes.io/ingress.class']= 'nginx'
    ingress['metadata']['annotations']['kubernetes.io/tls-acme']= "true"

    url=sid + '.jupyter.hypatia-comp.athenarc.gr'

    ispec={'rules':[],'tls':[]}

    rule={'host':url, 'http':{'paths':[{'backend':{'service':{'name':sname, 'port':{'number':80} } }, 'path':'/', 'pathType':'Prefix' } ] } }
                                            

                             

    tls={'hosts':[url], 'secretName': appName + '-ingress-secret'}

    ispec['rules'].append(rule)
    ispec['tls'].append(tls)

    ingress['spec']=ispec

    g=open(manifest,'w')
    yaml.dump(deployment, g, default_flow_style=False)
    g.write('\n\n---\n\n')
    yaml.dump(service, g, default_flow_style=False)
    g.write('\n\n---\n\n')
    yaml.dump(ingress, g, default_flow_style=False)
    g.close()
    
    return manifest,url




