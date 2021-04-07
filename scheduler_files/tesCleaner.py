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
import subprocess

def cleanJob(mounts,folder,jobName):
    configFileName=os.path.dirname(os.path.abspath(__file__)) + '/configuration.json'
    configFile=open(configFileName,'r')
    config=json.load(configFile)
    configFile.close()

    db=config['database']
    namespaces=config.get('namespaces',None)
    jobNamespace=None
    if namespaces is not None:
        jobNamespace=namespaces.get('jobs',None)


    if jobNamespace is not None:
        command="kubectl get pods -n " + jobNamespace + " --no-headers -l job-name=" + jobName + " | tr -s ' '"
    else:
        command="kubectl get pods --no-headers -l job-name=" + jobName + " | tr -s ' '"
    
    try:
        out=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        return 41

    podid=''
    out=out.split(' ')
    podid=out[0]

    if jobNamespace is not None:
        command="kubectl logs " + podid + " -n " + jobNamespace + " 2>&1"
    else:
        command="kubectl logs " + podid + " 2>&1"

    try:
        logs=subprocess.check_output(command,stderr=subprocess.STDOUT,shell=True, encoding='utf-8')
    except subprocess.CalledProcessError as exc:
        print(exc.output)
        return 42

    logFile=folder + '/logs.txt'
    g=open(logFile,'w')
    g.write(logs)
    g.close()

    for mount in mounts:
        folder=mount[0]
        try:
            subprocess.call(['rm','-rf', folder])
        except:
            pass

    return 0

    

