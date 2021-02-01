#!/usr/bin/python3
import sys
import subprocess
import psycopg2 as psg
import requests
import json
from requests.auth import HTTPBasicAuth
import os
import time
import uuid


configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

certsFolder=os.path.dirname(os.path.abspath(__file__)) + '/certificates/'
registry=config['registry']
registryUsername=config['registryAuth']['username']
registryPassword=config['registryAuth']['password']

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

imageName=sys.argv[1]
imageVersion=sys.argv[2]

#if image exists in only one software then delete it from the repository or else keep it intact
conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
sql="SELECT image FROM software WHERE name='" + imageName + "' AND version='" + imageVersion + "'"
cur.execute(sql)
results=cur.fetchone()
print(results)
fullImage=results[0]

sql="SELECT COUNT(*) FROM software WHERE image='" + fullImage + "'"
cur.execute(sql)
results=cur.fetchone()

count=results[0]
count=1
if count==1:
    image=imageName.lower() + '-' + imageVersion.lower()

    ##
    # If there is a process is uploading, wait for it to finish
    #
    sql="SELECT COUNT(*) FROM operation_locks"
    cur.execute(sql)
    results=cur.fetchone()
    opCount=results[0]
    while opCount>0:
        time.sleep(5)
        sql="SELECT COUNT(*) FROM operation_locks"
        cur.execute(sql)
        results=cur.fetchone()
        opCount=results[0]

    ##
    # Add a lock in the database
    ##
    uniqid=uuid.uuid4()
    uniqid=str(uniqid)
    sql="INSERT INTO operation_locks(id,operation) VALUES ('" + uniqid + "','image_delete')"
    cur.execute(sql)
    conn.commit()
    #######get the manifest in openstack
    url='https://' + registry + '/v2/' + image + '/manifests/latest'
    headers={'Accept':'application/vnd.docker.distribution.manifest.v2+json'}
    ssl=[certsFolder + 'client.crt',certsFolder +'client.key']
    ##
    # Comment the following line if you are using self-signed certificates
    ##
    response = requests.get(url,headers=headers, verify=True, auth=HTTPBasicAuth(registryUsername, registryPassword))
    ##
    # Uncomment the following line if you are using self-signed certificates
    ##
    # response2 = requests.delete(urlDel, cert=ssl, verify=False, auth=HTTPBasicAuth(registryUsername, registryPassword))

    if 'Docker-Content-Digest' in response.headers:
        digest=response.headers['Docker-Content-Digest']


        print(digest)

        ##
        # get initial pod name for restart
        ##
        command=['kubectl', 'get', 'pods', '-n', 'registry', '--no-headers']
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
            exit(49)
        pod=out.split()[0]



        ##### Remove in an openstack setting
        urlDel='https://' + registry + '/v2/' + image + '/manifests/' + digest
        ##
        # Comment the following line if you are using self-signed certificates
        ##
        response = requests.get(url,headers=headers, verify=True, auth=HTTPBasicAuth(registryUsername, registryPassword))
        ##
        # Uncomment the following line if you are using self-signed certificates
        ##
        # response2 = requests.delete(urlDel, cert=ssl, verify=False, auth=HTTPBasicAuth(registryUsername, registryPassword))

        ##
        #   Uncomment the following lines you you are using a docker registry inside the K8s cluster
        ##
        returnCode=subprocess.call('kubectl -n registry delete pod ' + pod.decode("utf-8") + ' 2>&1', shell=True)
        ##
        # get pod name after restart
        ##
        command=['kubectl', 'get', 'pods', '-n', 'registry', '--no-headers']
        try:
            out=subprocess.check_output(command,stderr=subprocess.STDOUT)
        except subprocess.CalledProcessError as exc:
            print(exc.output)
            exit(49)
        pod=out.split()[0]

        ##
        # Wait until all containers in the new pod are ready
        ##
        status=(out.split()[1]).decode("utf-8")
        while status!="1/1":
            time.sleep(3)
            command=['kubectl', 'get', 'pods', '-n', 'registry', '--no-headers']
            try:
                out=subprocess.check_output(command,stderr=subprocess.STDOUT)
            except subprocess.CalledProcessError as exc:
                print(exc.output)
                exit(49)
            status=(out.split()[1]).decode("utf-8")
        ##
        # Send garbage collection command to pod
        ##
        returnCode=subprocess.call('kubectl -n registry exec ' + pod.decode("utf-8") + ' -- registry garbage-collect /etc/docker/registry/config.yml 2>&1', shell=True)

        ##
        #   Uncomment the following lines you you are using a docker registry in the same machine
        ##
        # returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker container restart registry" 2>&1', shell=True)
        # returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker exec registry bin/registry garbage-collect /etc/docker/registry/config.yml" 2>&1', shell=True)

        ##
        #   Uncomment the following lines you you are using a docker registry created by juju
        ##
        # returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker container restart registry" 2>&1', shell=True)
        # returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker exec registry bin/registry garbage-collect /etc/docker/registry/config.yml" 2>&1', shell=True)


    sql="DELETE FROM operation_locks WHERE id='" + uniqid + "'"
    cur.execute(sql)
    conn.commit()

    print('Deleting from local')
    subprocess.call(['docker', 'rmi', registry + '/' + image + ':latest'])

 
#delete from DB

sql1="SELECT id FROM software WHERE name='" + imageName + "' AND version='" + imageVersion + "'"
cur.execute(sql1)
row=cur.fetchone()
softId=str(row[0])

sql2="DELETE FROM software WHERE id=" + softId
cur.execute(sql2)

sql3="DELETE FROM software_inputs WHERE softwareid=" + softId
cur.execute(sql3)

conn.commit()
conn.close()
