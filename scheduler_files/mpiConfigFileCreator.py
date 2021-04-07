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
from ruamel.yaml import YAML
from ruamel.yaml.scalarstring import PreservedScalarString as pss


def createFile(name,image,
    jobid,tmpFolder,
    imountPoint,isystemMount,
    omountPoint,osystemMount,
    iomountPoint,iosystemMount,
    maxMem,maxCores,pernode,nfsIp):
    
    jobName=name.lower().replace(' ','-').replace('\t','-') + '-' + jobid
    yamlName=tmpFolder + '/' + jobName + '.yaml'
    replicas=(int(maxCores)/int(pernode)) + 1
    # replicas=6
    cpuPerPod='7000m'
    memPerPod=maxMem + 'Gi'

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


    secret={}
    secret['apiVersion']='v1'
    secret['kind']='Secret'
    secret['metadata']={}
    secret['metadata']['name']='mpi-ssh-key'
    secret['metadata']['labels']={}
    secret['metadata']['labels']['app']='kube-openmpi'
    secret['metadata']['labels']['chart']='kube-openmpi-0.5'
    secret['metadata']['labels']['release']='mpi'
    secret['metadata']['labels']['heritage']='Helm'
    secret['type']='Opaque'
    secret['data']={}
    secret['data']['id_rsa']="LS0tLS1CRUdJTiBSU0EgUFJJVkFURSBLRVktLS0tLQpNSUlFcFFJQkFBS0NBUUVBN3ZudWRZaVB5bVpyTklaMUx2Y3dEeHByOWpObVhDeWVxc25IQ2hJNG03dEpDYmcxCkNBMmxWZ0ptWTJLUjdQVWEyZTEvU09IRTAzVmI5aG5BaE9QMU1jYzBnNEpWcDhycjg0ZjBoQ2FET2lJRUhQaFoKTFBDdUxIeFh4V1hMYWZvcXA3ajdNalhmUDlOYjVmQWxKZG1xNDlaYzRBd21LRE1yc2Z1U3M1L2F4Nm05UHlRawpoUEdpV0J6aVgyUE5qeDVicXN6VTlpcytoTTFWRDZSdE9mRDFvU01seHQ3R1FnOWtQRk5JTm4vK0tsRFhrSWlwCk05ZWpYY0VDTE41M3A2VUYzWVFWcVRpb09Yck8xUDJuckZ4SWJZRHRHUmw5ZXcvUjRWZVpVNmFSMzIrWkNTTGsKYmxleTJESTB5Y2ZpNEtNelNSU3hOcFJIYTlFZlJYbGRJNFd5QlFJREFRQUJBb0lCQUdoaTdnR1RGRlF4NXJRNwo0MllQZllPclkrdFlTbWNLNm9mcHViS3hnTjZ0c1ZxNGh4bXNkRU1jcTBMUVpMT2Y0UW0ranpIenhIa2xzM1ROCmpPVy9lWFF6OHNGYkpqVTBFMXIrVVJXRWlSL1VpZjFwa2ZKcWMzcGxHaVhVc0VUcmpNUlVyZzBoc1JIOUxoQkQKbE1aeXpRM1dyaDBEMFlzUHh3OW90NDBVejRrVXlNaTNBdTJmUEdYdElBMHRFSllZTnV3UnRQVUNpcEJJZFk2VApsV29YaHJocG9GdTdiS1ZhMDM1YnhkYkVtbzBPakl2bXRockN4NXlTK29HbE04U1dhVEMzU2p2VEdUdEp1WmR0CmQ1ZGhJakNJMGtpSUo4WVdhbmZXNVlNeUMxa0JwUURMS2RLTTJYdG5FaGFIclBNTDJheC9PcEZwWW1UWnZuZ3EKR2JzRmZXRUNnWUVBK1pkcEFzRGtQelE0M3RKWjJHbGczNkVPeHNqMkJtZXFqdEluck1palJKT0k4dFdnaFBIMApYc2JyRjhBSXpWcWdBY2hnM0s1Y3RoVU0rOFE3SFdSZGRyck85UlQvNTIvMFN0TWxKSGpNREdPZ3dLNk9URXF4CkYrOHg5SHZqZzc3OFNLZXNlT01TMkJhRG5XRm91QXk5bUQ1aDVPOGtVVzRuMCtaQmkvck93MDBDZ1lFQTlSeS8KTDZPYlo1Vk9HYWVFVkpLU3oyemlLbGJpaWlla3FJRjFkR044MlFBVCtQYlgyK0QvbGpZQVpBVmZtT050RzNuOApYNkhMSTRTS3QrbjFzVUlrYUdDUEtwV3h1bWhkU3BPUW8vN044NGp1b0ZxYXBHbUoxV3lhbFU4R1Jac3lYUjY0CjhFcG0rR1BlZmhxTDNmbzZDUWhpRnlIRE4rTUhhK05aVVNVQVhaa0NnWUVBbFZMaHI1VUp1VXFjRDZ0WHQyTHMKWXo2dllYeVB2S3MrM20yYThRT2tzbjExL0pxVWk1VmFOMjNZN3Yra0JJQUlwS2htVXdFNlZIRnBzQ0xwbng4Ugp6OXZadjhVTmlVQndybWQrbkVCdEM2aDdYMnZQbEpOSE9tT1B4bzVzUXNEN25OZzhGcUw3L2k0U0ZoRldIbTc0CjYyRy9IY0Y1UUFLZ2IyVWRxQXFvc3kwQ2dZRUFyYnlyazJQZlFtT2xFVEFZN3kxWm5HY3NSQ0NEd0xOcTFmbTEKUEVOTWVpL0ErR1pYQ01hSnczb1JldFFJTjhGMFU5WjZXWHJBRnpQYWM1UUZENWkzWDdpWm9mQ3JJbHJaVFlRZApNUERxSUdYOTVuRnlUWGwxTm04ZGZ4bWRjM2NYRXNRMUNEVmttVW1JcWsrOHRpR29RMERLN21TREVEait5SzFFCllPemVQbmtDZ1lFQW1LNTVzQmNBdXo4MVVHUVYyWUlKSE12a0JWNTFjNGh2VmplaWZ0dWpFYmNZb3RJYTUxK1YKb0RyMTE4d3k4UFpOQTVmc09OTktrMExJbmV1dTVRSzZpM2dxRkp3NWxFdHVCYTc0Vm8zN3ZQWjF6THVoZUl1cgp0QjZ1Ni9Wb2tMYjIzVkEwU1Fuc214cTRrdmVUTFNMT2VlTU9JTmR4RTJmejZvNm1PWjExTXVjPQotLS0tLUVORCBSU0EgUFJJVkFURSBLRVktLS0tLQo="
    secret['data']['id_rsa.pub']="c3NoLXJzYSBBQUFBQjNOemFDMXljMkVBQUFBREFRQUJBQUFCQVFEdStlNTFpSS9LWm1zMGhuVXU5ekFQR212Mk0yWmNMSjZxeWNjS0VqaWJ1MGtKdURVSURhVldBbVpqWXBIczlSclo3WDlJNGNUVGRWdjJHY0NFNC9VeHh6U0RnbFdueXV2emgvU0VKb002SWdRYytGa3M4SzRzZkZmRlpjdHAraXFudVBzeU5kOC8wMXZsOENVbDJhcmoxbHpnRENZb015dXgrNUt6bjlySHFiMC9KQ1NFOGFKWUhPSmZZODJQSGx1cXpOVDJLejZFelZVUHBHMDU4UFdoSXlYRzNzWkNEMlE4VTBnMmYvNHFVTmVRaUtrejE2TmR3UUlzM25lbnBRWGRoQldwT0tnNWVzN1UvYWVzWEVodGdPMFpHWDE3RDlIaFY1bFRwcEhmYjVrSkl1UnVWN0xZTWpUSngrTGdvek5KRkxFMmxFZHIwUjlGZVYwamhiSUYgdWJ1bnR1QHNjaGVtYS1qdWp1Cg=="
    secret['data']['authorized_keys']="c3NoLXJzYSBBQUFBQjNOemFDMXljMkVBQUFBREFRQUJBQUFCQVFEdStlNTFpSS9LWm1zMGhuVXU5ekFQR212Mk0yWmNMSjZxeWNjS0VqaWJ1MGtKdURVSURhVldBbVpqWXBIczlSclo3WDlJNGNUVGRWdjJHY0NFNC9VeHh6U0RnbFdueXV2emgvU0VKb002SWdRYytGa3M4SzRzZkZmRlpjdHAraXFudVBzeU5kOC8wMXZsOENVbDJhcmoxbHpnRENZb015dXgrNUt6bjlySHFiMC9KQ1NFOGFKWUhPSmZZODJQSGx1cXpOVDJLejZFelZVUHBHMDU4UFdoSXlYRzNzWkNEMlE4VTBnMmYvNHFVTmVRaUtrejE2TmR3UUlzM25lbnBRWGRoQldwT0tnNWVzN1UvYWVzWEVodGdPMFpHWDE3RDlIaFY1bFRwcEhmYjVrSkl1UnVWN0xZTWpUSngrTGdvek5KRkxFMmxFZHIwUjlGZVYwamhiSUYgdWJ1bnR1QHNjaGVtYS1qdWp1Cg=="

    configMap={}
    configMap['apiVersion']='v1'
    configMap['kind']='ConfigMap'
    configMap['metadata']={}
    configMap['metadata']['name']='mpi-assets'
    configMap['metadata']['labels']={}
    configMap['metadata']['labels']['app']='kube-openmpi'
    configMap['metadata']['labels']['chart']='kube-openmpi-0.5'
    configMap['metadata']['labels']['release']='mpi'
    configMap['metadata']['labels']['heritage']='Helm'
    configMap['data']={}
    configMap['data']['gen_hostfile.sh']=pss("""\
        set -xev

        target=$1
        max_try=$2

        trap \"rm -f ${target}_new\" EXIT TERM INT KILL

        cluster_size=$(kubectl -n mpi-cluster get statefulsets mpi-worker -o jsonpath='{.spec.replicas}')
        

        tried=0
        until [ \"$(wc -l < ${target}_new)\" -eq $cluster_size ]; do

          pod_names=$(kubectl -n mpi-cluster get pod \
            --selector=app=kube-openmpi,chart=kube-openmpi-0.5,release=mpi,role=worker \
            --field-selector=status.phase=Running \
            -o=jsonpath='{.items[*].status.podIP}')

          rm -f ${target}_new
          for p in ${pod_names}; do
            echo \"${p}\" >> ${target}_new
          done

          tried=$(expr $tried + 1)
          if [ -n \"$max_try\" ] && [ $max_try -ge $tried ]; then
            break
          fi
        done

        pod_names=$(kubectl -n mpi-cluster get pod \
            --selector=app=kube-openmpi,chart=kube-openmpi-0.5,release=mpi,role=master \
            --field-selector=status.phase=Running \
            -o=jsonpath='{.items[*].status.podIP}')

          for p in ${pod_names}; do
            echo \"${p}\" >> ${target}_new
          done

        if [ -e ${target}_new ]; then
          mv ${target}_new ${target}
        fi""")

    configMap['data']['hostfile_update_every']="15"

    service={}
    service['apiVersion']='v1'
    service['kind']='Service'
    service['metadata']={}
    service['metadata']['name']='mpi-assets'
    service['metadata']['labels']={}
    service['metadata']['labels']['app']='kube-openmpi'
    service['metadata']['labels']['chart']='kube-openmpi-0.5'
    service['metadata']['labels']['release']='mpi'
    service['metadata']['labels']['heritage']='Helm'
    service['spec']={}
    service['spec']['selector']={}
    service['spec']['selector']['app']='kube-openmpi'
    service['spec']['selector']['release']='mpi'
    service['spec']['clusterIP']='None'
    service['spec']['ports']=[{'name':'dummy','port':1234,'targetPort':1234}]

    master={}
    master['apiVersion']='v1'
    master['kind']='Pod'
    master['metadata']={}
    master['metadata']['name']='mpi-master'
    master['metadata']['labels']={}
    master['metadata']['labels']['app']='kube-openmpi'
    master['metadata']['labels']['chart']='kube-openmpi-0.5'
    master['metadata']['labels']['release']='mpi'
    master['metadata']['labels']['heritage']='Helm'
    master['metadata']['labels']['role']='master'
    master['spec']={}
    master['spec']['restartPolicy']='OnFailure'
    master['spec']['hostname']='mpi'
    master['spec']['securityContext']=None
    master['spec']['volumes']=[]
    master['spec']['volumes'].append({'name': 'kube-openmpi-guillotine','emptyDir': {}})
    master['spec']['volumes'].append({'name': 'kube-openmpi-hostfile-dir','emptyDir': {}})
    hostupd={'name': 'mpi-assets','items':[{'key':'hostfile_update_every', 'path':'update_every'}]}
    master['spec']['volumes'].append({'name': 'kube-openmpi-hostfile-updater-params','configMap': hostupd})
    utils={'name': 'mpi-assets','items':[{'key':'gen_hostfile.sh', 'path':'gen_hostfile.sh', 'mode':365}]}
    master['spec']['volumes'].append({'name': 'kube-openmpi-utils','configMap': utils})
    master['spec']['volumes'].append({'name': 'kube-openmpi-ssh-key','secret': {'secretName':'mpi-ssh-key','defaultMode': 256}})
    master['spec']['volumes']+=volumes
    
    container={}
    container['name']='hostfile-initializer'
    container['image']='everpeace/kubectl:1.9.2'
    container['imagePullPolicy']='IfNotPresent'
    container['command']=['sh','-c',pss("""/kube-openmpi/utils/gen_hostfile.sh $HOSTFILE_DIR/hostfile""")]
    container['env']=[{'name': 'HOSTFILE_DIR', 'value': '/kube-openmpi/generated'}]
    container['volumeMounts']=[{'name': 'kube-openmpi-hostfile-dir', 'mountPath': '/kube-openmpi/generated'},
                                {'name': 'kube-openmpi-utils', 'mountPath': '/kube-openmpi/utils'}]

    master['spec']['initContainers']=[container]

    
    container1={}
    container2={}
    container1['name']='mpi-master'
    container1['image']=image
    container1['imagePullPolicy']='IfNotPresent'
    container1['ports']=[{'containerPort':2022}]
    container1['env']=[{'name': 'HOSTFILE', 'value': '/kube-openmpi/generated/hostfile'},{'name': 'GUILLOTINE', 'value': '/kube-openmpi/guillotine'}]
    container1['volumeMounts']=[{'name': 'kube-openmpi-guillotine', 'mountPath': '/kube-openmpi/guillotine'},
                                {'name': 'kube-openmpi-ssh-key', 'mountPath': '/ssh-key/openmpi'},
                                {'name': 'kube-openmpi-hostfile-dir', 'mountPath': '/kube-openmpi/generated/'},
                                {'name': 'kube-openmpi-utils', 'mountPath': '/kube-openmpi/utils'},]
    container1['volumeMounts']+=mounts
    limits={'cpu':cpuPerPod, 'memory': memPerPod}
    requests={'cpu':cpuPerPod, 'memory': memPerPod}
    container1['resources']={'limits':limits,'requests':requests}

    container2['name']='hostfile-updater'
    container2['image']='everpeace/kubectl:1.9.2'
    container2['imagePullPolicy']='IfNotPresent'
    container2['command']=['sh','-c',pss("""\
      while [ ! -e $GUILLOTINE/execute ];
      do
        /kube-openmpi/utils/gen_hostfile.sh $HOSTFILE_DIR/hostfile 1
        if [ -e /kube-openmpi/hostfile-updater-params/update_every ]; then
          SLEEP=$(cat /kube-openmpi/hostfile-updater-params/update_every)
        fi
        sleep ${SLEEP:-15}
      done
      echo Done.
    """)]
    container2['env']=[{'name': 'HOSTFILE_DIR', 'value': '/kube-openmpi/generated'},{'name': 'GUILLOTINE', 'value': '/kube-openmpi/guillotine'}]
    container2['volumeMounts']=[{'name': 'kube-openmpi-guillotine', 'mountPath': '/kube-openmpi/guillotine'},
                                {'name': 'kube-openmpi-hostfile-updater-params', 'mountPath': '/kube-openmpi/hostfile-updater-params'},
                                {'name': 'kube-openmpi-hostfile-dir', 'mountPath': '/kube-openmpi/generated/'},
                                {'name': 'kube-openmpi-utils', 'mountPath': '/kube-openmpi/utils'},]
    master['spec']['containers']=[container1,container2]


    worker={}
    worker['apiVersion']='apps/v1'
    worker['kind']='StatefulSet'
    worker['metadata']={}
    worker['metadata']['name']='mpi-worker'
    worker['metadata']['labels']={}
    worker['metadata']['labels']['app']='kube-openmpi'
    worker['metadata']['labels']['chart']='kube-openmpi-0.5'
    worker['metadata']['labels']['release']='mpi'
    worker['metadata']['labels']['heritage']='Helm'
    worker['metadata']['labels']['role']='worker'
    worker['spec']={}
    worker['spec']['selector']={}
    worker['spec']['selector']['matchLabels']={}
    worker['spec']['selector']['matchLabels']['app']='kube-openmpi'
    worker['spec']['selector']['matchLabels']['chart']='kube-openmpi-0.5'
    worker['spec']['selector']['matchLabels']['release']='mpi'
    worker['spec']['selector']['matchLabels']['heritage']='Helm'
    worker['spec']['selector']['matchLabels']['role']='worker'
    worker['spec']['serviceName']='mpi'
    worker['spec']['podManagementPolicy']='Parallel'
    worker['spec']['replicas']=replicas
    worker['spec']['template']={}
    worker['spec']['template']['metadata']={}
    worker['spec']['template']['metadata']['name']='mpi-worker'
    worker['spec']['template']['metadata']['labels']={}
    worker['spec']['template']['metadata']['labels']['app']='kube-openmpi'
    worker['spec']['template']['metadata']['labels']['chart']='kube-openmpi-0.5'
    worker['spec']['template']['metadata']['labels']['release']='mpi'
    worker['spec']['template']['metadata']['labels']['heritage']='Helm'
    worker['spec']['template']['metadata']['labels']['role']='worker'
    worker['spec']['template']['spec']={}
    worker['spec']['template']['spec']['securityContext']=None
    worker['spec']['template']['spec']['volumes']=[]
    worker['spec']['template']['spec']['volumes'].append({'name': 'kube-openmpi-ssh-key','secret': {'secretName':'mpi-ssh-key','defaultMode': 256}})
    utils={'name': 'mpi-assets','items':[{'key':'gen_hostfile.sh', 'path':'gen_hostfile.sh', 'mode':365}]}
    worker['spec']['template']['spec']['volumes'].append({'name': 'kube-openmpi-utils','configMap': utils})
    worker['spec']['template']['spec']['volumes']+=volumes
    container={'name':'mpi-worker'}
    container['image']=image
    container['imagePullPolicy']='IfNotPresent'
    container['ports']=[{'containerPort':2022}]
    container['volumeMounts']=[{'name': 'kube-openmpi-ssh-key', 'mountPath': '/ssh-key/openmpi'},
                                {'name': 'kube-openmpi-utils', 'mountPath': '/kube-openmpi/utils'},]
    container['volumeMounts']+=mounts
    limits={'cpu':cpuPerPod, 'memory': memPerPod}
    requests={'cpu':cpuPerPod, 'memory': memPerPod}
    container['resources']={'limits':limits,'requests':requests}
    worker['spec']['template']['spec']['initContainers']=None
    worker['spec']['template']['spec']['containers']=[container]

    yaml = YAML()


    g=open(yamlName,'w')
    yaml.dump(secret, g)
    g.write('\n---\n')
    yaml.dump(configMap, g)
    g.write('\n---\n')
    yaml.dump(service, g)
    g.write('\n---\n')
    yaml.dump(master, g)
    g.write('\n---\n')
    yaml.dump(worker, g)

    g.close()

    return yamlName

