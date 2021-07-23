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
import psycopg2 as psg
import sys
import yaml
import subprocess
import datetime
import json
import os

configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
configFile=open(configFileName,'r') 
config=json.load(configFile)
configFile.close()

db=config['database']
host=db['host']
dbuser=db['username']
passwd=db['password']
dbname=db['database']

softName=sys.argv[1]
softVersion=sys.argv[2]
cwlPath=sys.argv[3]


def quoteEnclose(string):
    return "'" + string + "'"

f=open(cwlPath,'r')
try:
    content=yaml.load(f)
except:
    exit(2)
f.close()
conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()

# print(content)
if 'baseCommand' in content:
    if isinstance(content['baseCommand'],list):
        command=' '.join(content['baseCommand'])
    else:
        command=content['baseCommand']
    if (command=='') or (command==None):
        exit(7)
    
    query="UPDATE software SET script=" + quoteEnclose(command) + " WHERE name=" + quoteEnclose(softName) + "AND version=" + quoteEnclose(softVersion)

    cur.execute(query)
else:
    exit(8)

try:
    inputs=content['inputs']
except:
    conn.commit()
    conn.close()
    exit(4)



types=set(['string', 'int', 'long', 'float', 'double', 'null', 'File', 'Directory', 'Any','boolean'])

#open db connection and get image id
conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
cur=conn.cursor()

query="SELECT id FROM software WHERE name='" + softName + "' AND version='" + softVersion + "'"
cur.execute(query)
result=cur.fetchall()
softId=str(result[0][0])

#delete previous input list
queryDel="DELETE from software_inputs where softwareid=" + softId
cur.execute(queryDel)
conn.commit()

if len(inputs)==0:
    exit(4)




#create queries for input insertion
query='INSERT INTO software_inputs(name, position, softwareid, field_type, prefix, separate, optional, default_value) VALUES '

bindingFlag=False
positionFlag=False

for inpt in inputs:
    if 'inputBinding' not in inpt.keys():
        bindingFlag=True
        continue
        #exit(32)
    
    binding=inpt['inputBinding']
    # Get position, separate and prefix from inputBinding.
    # If it does not exist, ignore input
    if 'position' not in binding:
        positionFlat=True
        continue
    position=binding['position']

    separate='t'
    if 'separate' in binding:
        if binding['separate']=='false':
            separate='f'
        else:
            separate='t'

    prefix=''
    if 'prefix' in binding:
        prefix=binding['prefix']
        
   

    # Get field type and optional
    if 'type' not in inpt.keys():
        #stop execution and return because this is serious
        exit(34)

    fieldType=inpt['type'].strip()

    optional='f'
    if fieldType[-1]=='?':
        optional='t'
        fieldType=fieldType[:-1]
    
    if fieldType not in types:
        #stop execution and return because this is serious
        print(fieldType)
        exit(35)
    
    
    #get default value
    defaultValue=''
    if (fieldType!='File') and (fieldType!='Directory') and (fieldType!='null'):
        if 'default' in inpt.keys():
            defaultValue=str(inpt['default'])

    name=quoteEnclose(inpt['id'])
    fieldType=quoteEnclose(fieldType)
    prefix=quoteEnclose(prefix)
    defaultValue=quoteEnclose(defaultValue)
    optional=quoteEnclose(optional)
    separate=quoteEnclose(separate)

    query+='(' + name + ',' + str(position) + ',' + str(softId) + ',' + fieldType + ',' + prefix + ',' + separate + ',' + optional + ',' + defaultValue + '),'

query=query[:-1]
print(query)
cur.execute(query)
conn.commit()

conn.close()

if bindingFlag:
    exit(9)
if positionFlag:
    exit(33)
