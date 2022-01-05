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
import json
import datetime
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

def getMainWorkflowFile(folder,allowed):
    found=False
    mainFile=''
    mainContent=''
    for root, dirs, files in os.walk(folder, topdown=False):
        for name in files:
            file=os.path.join(root,name)
            f, ext=os.path.splitext(name)
            ext=ext.strip('.')
            print('File: ' + file)
            if '__MACOSX' in file:
                continue
            if ext not in allowed:
                continue
            # Read the file of the class.
            # If it is not yaml or yaml syntax is wrong, return an error code.
            f=open(file,encoding='utf8',mode='r')
            try:
                content=yaml.load(f,Loader=yaml.FullLoader)
            except Exception as e:
                print(e)
                print(file)
                return False,11,{}
            f.close()
            
            # If class does not exist in file, return an error code.
            try:
                fclass=content['class']
            except:
                return False,12,{}

            # If file is CommandLineTool or something else try another file.
            if (fclass=='Workflow'):
                # If the folder has more than one main files, return an error code.
                if (found==True):
                    return False,13,{}
                mainFile=file
                mainContent=content
                found=True
            else:
                continue
    # If no main workflow file was found, return an error code.
    if found==False:
        return False,14,{}
    print('Main file: ' + mainFile)
    return (mainFile,0,mainContent)


def deleteSavedWorkflow(name,version):
    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()

    query="SELECT id FROM workflow_upload WHERE name='" + name + "' AND version='" + version + "' ORDER BY date DESC LIMIT 1"
    cur.execute(query)
    result=cur.fetchall()
    workuploadId=str(result[0][0])

    query="DELETE FROM workflow_upload where id=" + str(workuploadId)
    cur.execute(query)

    query="DELETE FROM workflow WHERE name='" + name + "' AND version='" + version + "'"
    cur.execute(query)
    conn.commit()
    conn.close()


def inputStoreDict(workName, workVersion, inputs):
    types=set(['string', 'int', 'long', 'float', 'double', 'null', 'File', 'Directory', 'Any','boolean'])
    #open db connection and get image id
    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()

    query="SELECT id FROM workflow WHERE name='" + workName + "' AND version='" + workVersion + "'"
    cur.execute(query)
    result=cur.fetchall()
    softId=str(result[0][0])

    #save script in database and get its id
    if len(inputs)==0:
        exit(30)

    #create queries for input insertion
    query='INSERT INTO workflow_inputs(name, position, workflow_id, field_type, prefix, separate, optional, default_value, enum_fields, is_array) VALUES '

    pos=0
    for inpt in inputs:
        print(inpt)
        position=pos
        pos+=1
        # print(inpt)
        enum_fields=''
        name=inpt

        separate='t'
        optional='f'
        prefix=''
        is_array='f'
        
        
       
        # print(inpt)
        # Get field type and optional
        if 'type' not in inputs[inpt]:
            #stop execution and return because this is serious
            deleteSavedWorkflow(workName,workVersion)
            return 34
        

        fieldType=inputs[inpt]['type']

        # If input is type enum or array
        if isinstance(fieldType,dict):
            if 'type' not in fieldType:
                deleteSavedWorkflow(workName,workVersion)
                return(36)

            if fieldType['type']=='enum':
                if 'symbols' not in fieldType:
                    deleteSavedWorkflow(workName,workVersion)
                    return(38)
                symbols=fieldType['symbols']
                enum_fields='|'.join(symbols)
                fieldType='enum'
            elif fieldType['type']=='array':
                is_array='t'
                if 'items' not in fieldType:
                    deleteSavedWorkflow(workName,workVersion)
                    return(37)
                fieldType=fieldType['items']
            else:
                deleteSavedWorkflow(workName,workVersion)
                return(39)

        else:
            if 'separate' in inputs[inpt]:
                if inputs[inpt]['separate']=='false':
                    separate='f'
                else:
                    separate='t'

        
            if 'prefix' in inputs[inpt]:
                prefix=inputs[inpt]['prefix']

            fieldType=inputs[inpt]['type'].strip()

            if fieldType[-1]=='?':
                optional='t'
                fieldType=fieldType[:-1]
            if '[]' in fieldType:
                is_array='t'
                fieldType=fieldType[:-2]
            
            if (fieldType not in types) and (fieldType!='enum'):
                #stop execution and return because this is serious
                deleteSavedWorkflow(workName,workVersion)
                # print(fieldType)
                return 35
        
        #get default value
        defaultValue=''
        if (fieldType!='File') and (fieldType!='Directory') and (fieldType!='null'):
            if 'default' in inputs[inpt]:
                defaultValue=str(inputs[inpt]['default'])

        name=quoteEnclose(name)
        fieldType=quoteEnclose(fieldType)
        prefix=quoteEnclose(prefix)
        defaultValue=quoteEnclose(defaultValue)
        optional=quoteEnclose(optional)
        separate=quoteEnclose(separate)
        enum_fields=quoteEnclose(enum_fields)
        is_array=quoteEnclose(is_array)

        query+='(' + name + ',' + str(position) + ',' + str(softId) + ',' + fieldType + ',' + prefix + ',' + separate + ',' + optional + ',' + defaultValue + ',' + enum_fields + ',' + is_array + '),'

    query=query[:-1]
    # print(query)
    cur.execute(query)
    conn.commit()

    conn.close()


    return 0

def inputStoreList(workName, workVersion, inputs):
    types=set(['string', 'int', 'long', 'float', 'double', 'null', 'File', 'Directory', 'Any','boolean'])

    #open db connection and get image id
    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()

    query="SELECT id FROM workflow WHERE name='" + workName + "' AND version='" + workVersion + "'"
    cur.execute(query)
    result=cur.fetchall()
    softId=str(result[0][0])

    #save script in database and get its id
    # print(inputs)
    if len(inputs)==0:
        exit(30)

    #create queries for input insertion
    query='INSERT INTO workflow_inputs(name, position, workflow_id, field_type, prefix, separate, optional, default_value, enum_fields, is_array) VALUES '

    pos=0
    for inpt in inputs:
        position=pos
        pos+=1
        # print(inpt)
        enum_fields=''
        name=inpt['id']

        separate='t'
        optional='f'
        prefix=''
        is_array='f'
        
            
       
        # print(inpt)
        # Get field type and optional
        if 'type' not in inpt:
            #stop execution and return because this is serious
            deleteSavedWorkflow(workName,workVersion)
            return 34
        
        fieldType=inpt['type']
        # If input is type enum
        if isinstance(fieldType,dict):
            if 'type' not in fieldType:
                deleteSavedWorkflow(workName,workVersion)
                return(36)

            # if fieldType['type']!='enum':
            #     deleteSavedWorkflow(workName,workVersion)
            #     return(37)
            if fieldType['type']=='enum':
                if 'symbols' not in fieldType:
                    deleteSavedWorkflow(workName,workVersion)
                    return(38)
                symbols=fieldType['symbols']
                enum_fields='|'.join(symbols)
                fieldType='enum'
            elif fieldType['type']=='array':
                is_array='t'
                if 'items' not in fieldType:
                    deleteSavedWorkflow(workName,workVersion)
                    return(37)
                fieldType=fieldType['items']
            else:
                deleteSavedWorkflow(workName,workVersion)
                return(39)
        else:
            print(name)

            if 'separate' in inpt:
                if inpt['separate']=='false':
                    separate='f'
                else:
                    separate='t'

        
            if 'prefix' in inpt:
                prefix=inpt['prefix']

            fieldType=inpt['type'].strip()

            if fieldType[-1]=='?':
                optional='t'
                fieldType=fieldType[:-1]
            
            if '[]' in fieldType:
                fieldType=fieldType[:-2]
                is_array='t'

            if fieldType not in types:
                #stop execution and return because this is serious
                deleteSavedWorkflow(workName,workVersion)
                print(fieldType)
                return 35
        
        
        #get default value
        defaultValue=''
        if (fieldType!='File') and (fieldType!='Directory') and (fieldType!='null'):
            if 'default' in inpt:
                defaultValue=str(inpt['default'])

        name=quoteEnclose(name)
        fieldType=quoteEnclose(fieldType)
        prefix=quoteEnclose(prefix)
        defaultValue=quoteEnclose(defaultValue)
        optional=quoteEnclose(optional)
        separate=quoteEnclose(separate)
        enum_fields=quoteEnclose(enum_fields)
        is_array=quoteEnclose(is_array)

        query+='(' + name + ',' + str(position) + ',' + str(softId) + ',' + fieldType + ',' + prefix + ',' + separate + ',' + optional + ',' + defaultValue + ',' + enum_fields+ ',' + is_array + '),'

    query=query[:-1]
    # print(query)
    cur.execute(query)
    conn.commit()

    conn.close()


    return 0

def workflowStore(name,version,location,user,visibility,
                description,biotools,doiFile,github_link,covid19,original_file,instructions):
    
    name=quoteEnclose(name)
    version=quoteEnclose(version)
    location=quoteEnclose(location)
    user=quoteEnclose(user)
    visibility=quoteEnclose(visibility)
    description=quoteEnclose(description)
    biotools=quoteEnclose(biotools);
    github_link=quoteEnclose(github_link)
    covid19=quoteEnclose(covid19)
    original_file=quoteEnclose(original_file)
    instructions=quoteEnclose(instructions)

    if doiFile!='':
        f=open(doiFile)
        dois=f.readline().strip()
        f.close()
    else:
        dois=''
    dois=quoteEnclose(dois)

    date="NOW()"

    values=[name,version,location,user,date,visibility,description,biotools,dois,covid19,github_link,original_file,instructions]
    
    sql1='INSERT INTO workflow_upload (name,version, location ,uploaded_by, date, visibility, \
                    description,biotools,dois,covid19,github_link,original_file,instructions) '
    sql1+='VALUES (' + ','.join(values) + ')'

    # print(sql1);

    
    ## classify software

    # ontologyFolder="/data/www/schema/scheduler_files/ontology/";
    # command=[ontologyFolder + 'initialClassify.py', softFull, '100', '100','64', '0', '0']
    # # print(' '.join(command))
    # # exit(0)
    # try:
    #     out=subprocess.check_output(command,stderr=subprocess.STDOUT)
    # except subprocess.CalledProcessError as exc:
    #     print(exc.output)
    #     exit(24)

    values=[name,version,location,user, visibility,description,biotools,
                    dois,github_link,covid19,original_file,instructions]
    sql2='INSERT INTO workflow (name,version,location,uploaded_by,\
            visibility, description,biotools,dois,github_link,covid19,original_file,instructions) '
    sql2+='VALUES (' + ','.join(values) + ')'
    
    # print()
    # print(sql2)
    conn=psg.connect(host=host, user=dbuser, password=passwd, dbname=dbname)
    cur=conn.cursor()
    cur.execute(sql1)
    cur.execute(sql2)
    conn.commit()
    conn.close()


def quoteEnclose(string):
    return "'" + string + "'"
