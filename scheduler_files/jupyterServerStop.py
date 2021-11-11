#!/usr/bin/python3
import psycopg2 as psg
import subprocess
import json
import os
import sys

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

sid=sys.argv[1]
user=sys.argv[2]

sql="SELECT id,manifest,server_id FROM jupyter_server WHERE server_id='" + sid + "'"

conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()
cur.execute(sql)
print(sql)
result=cur.fetchone();

print(result);
manifest=result[1]


subprocess.call(['kubectl', 'delete', '-f', manifest])

sql="UPDATE jupyter_server SET deleted_by='" + user + "', deleted_at=NOW(), active='f' WHERE server_id='" + sid + "'"
cur.execute(sql)

conn.commit()

conn.close()