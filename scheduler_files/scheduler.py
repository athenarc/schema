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
import configFileCreator as cfile
import sys
import subprocess
import os

name=sys.argv[1]
version=sys.argv[2]
image=sys.argv[3]
jobid=sys.argv[4]
tmpFolder=sys.argv[5]
workingDir=sys.argv[6]
imountPoint=sys.argv[7]
isystemMount=sys.argv[8]
omountPoint=sys.argv[9]
osystemMount=sys.argv[10]
iomountPoint=sys.argv[11]
iosystemMount=sys.argv[12]
maxMem=sys.argv[13]
maxCores=sys.argv[14]
nfsIp=sys.argv[15]
machineType=sys.argv[16]
sharedFolder=sys.argv[17]



# create yaml file
yamlFile=cfile.createFile(name,machineType,image,
						jobid,tmpFolder,workingDir,
						imountPoint,isystemMount,
						omountPoint,osystemMount,
						iomountPoint,iosystemMount,
						maxMem,maxCores,nfsIp,sharedFolder)

# deploy
k8sRetCode=subprocess.call(['kubectl','create','-f',yamlFile])
exit(k8sRetCode)




