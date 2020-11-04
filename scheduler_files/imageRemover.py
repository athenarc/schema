#!/usr/bin/env python
import sys
import subprocess
import psycopg2 as psg
import requests
import json
from requests.auth import HTTPBasicAuth
import os


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

fullImage=results[0]

sql="SELECT COUNT(*) FROM software WHERE image='" + fullImage + "'"
cur.execute(sql)
results=cur.fetchone()

count=results[0]
if count==1:
    image=imageName + '-' + imageVersion


    #######get the manifest in openstack
    url='https://' + registry + '/v2/' + image + '/manifests/latest'
    headers={'Accept':'application/vnd.docker.distribution.manifest.v2+json'}
    ssl=[certsFolder + 'client.crt',certsFolder +'client.key']
    response = requests.get(url,headers=headers, cert=ssl, verify=False, auth=HTTPBasicAuth(registryUsername, registryPassword))

    if 'Docker-Content-Digest' in response.headers:
        digest=response.headers['Docker-Content-Digest']


        print(digest)


        ##### Remove in an openstack setting
        urlDel='https://' + registry + '/v2/' + image + '/manifests/' + digest
        response2 = requests.delete(urlDel, cert=ssl, verify=False, auth=HTTPBasicAuth(registryUsername, registryPassword))
        returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker container restart registry" 2>&1', shell=True)
        returnCode=subprocess.call('sudo juju run --unit docker-registry/0 "docker exec registry bin/registry garbage-collect /etc/docker/registry/config.yml" 2>&1', shell=True)

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
