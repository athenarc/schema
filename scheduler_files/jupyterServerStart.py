#!/usr/bin/python3
import psycopg2 as psg
import subprocess
import json
import os
import sys
import jupyterConfig as cf

def enclose(s):
    return "'" + s + "'"

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r')
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

namespace=config['namespaces']['jupyter']
domain=config['jupyter']['domain']
platform=config['platform']

serverConfigFileName=sys.argv[1]
sConfigFile=open(serverConfigFileName,'r')
sconfig=json.load(sConfigFile)
sConfigFile.close()

sid=sconfig['id']
folder=sconfig['folder']
cpu=sconfig['resources']['cpu']
mem=sconfig['resources']['cpu']
image=sconfig['image']
password=sconfig['password']
project=sconfig['project']
mount=sconfig['mountFolder']
nfs=sconfig['nfs']
user=sconfig['user']
expires=sconfig['expires']


manifest,url=cf.createServerConfig(sid,cpu,mem,password,folder,image,mount,nfs,namespace,domain,platform)
print(manifest)
print(url)

subprocess.call(['kubectl', 'apply', '-f', manifest])

values=[enclose(manifest), enclose(project), enclose(sid), enclose(image), 'NOW()', enclose(user), enclose('https://' + url),"'t'", enclose(expires)]
sql='INSERT INTO jupyter_server(manifest,project,server_id,image,created_at,created_by,url,active, expires_on) VALUES (' + ','.join(values) + ')'

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
cur.execute(sql)
conn.commit()
conn.close()
