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

#Database configuration
host='localhost'
user='middleware'
passwd='M@d@r1n!'
dbname='middleware'

softName=sys.argv[1]
softVersion=sys.argv[2]
yamlFile=sys.argv[3]

#Read yaml file content
f=open(yamlFile,'r')
try:
    content=yaml.load(f)
except:
    exit(6)
f.close()

script="'" + content['baseCommand'] + "'"
inputs=content['inputs']

#open db connection and get image id
conn=psg.connect(host=host, user=user, password=passwd, dbname=dbname)
cur=conn.cursor()

query="SELECT id FROM software WHERE name='" + softName + "' AND version='" + softVersion + "'"
cur.execute(query)
result=cur.fetchall()
softId=str(result[0][0])

query="UPDATE software SET script=" + script + " WHERE name='" + softName + "' AND version='" + softVersion + "'"
cur.execute(query)
conn.commit()


#create queries for input insertion
query='INSERT INTO software_inputs(name, position, softwareid, field_type, prefix) VALUES '
    for inpt in inputs:
        if 'inputBinding' not in inputs[inpt]:
            continue
        position=inputs[inpt]['inputBinding']['position']
        
        if 'type' in inputs[inpt]:
            fieldType=inputs[inpt]['type']
        else:
            fieldType=''
        if 'prefix'in inputs[inpt]['inputBinding']:
            prefix=inputs[inpt]['inputBinding']['prefix']
        else:
            prefix=''

        name=quoteEnclose(inpt)
        fieldType=quoteEnclose(fieldType)
        prefix=quoteEnclose(prefix)

        query+='(' + name + ',' + str(position) + ',' + str(softId) + ',' + fieldType + ',' + prefix + '),'

query=query[:-1]
cur.execute(query)
conn.commit()


conn.close()

