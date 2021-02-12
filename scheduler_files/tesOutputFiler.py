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
from ftplib import FTP
import re

def uploadOutput(data,config,folder):

    patt=re.compile("ftp://([^/ ]+)/.+")
    patt2=re.compile("ftp://[^/ ]+/(.+)")

    #
    # If there are no outputs, then upload nothing and return
    #
    try:
        outputs=data['outputs']
    except:
        return 0

    for output in outputs:

        try:
            url=output['url']
        except:
            return 31,set()
        
        try:
            path=output['path']
        except:
            return 32,set()

        path=path.split('/')
        #
        # Get local folder and file path.
        #
        folderName=path[-2]
        localFilename=path[-1]

        localFolder=folder + '/' + folderName
        localFilepath=localFolder + '/' + localFilename


        urlTok=url.split(':')

        try:
            remoteFilename=url.split('/')[-1]
        except:
            return 33,set()
        try:
            server=patt.match(url).groups()[0]
        except:
            return 34
        try:
            creds=config['ftp-creds'][server]
        except:
            return 35

        try:
            remoteFilePath=patt2.match(url).groups()[0]
        except:
            return 34

        username=creds['username']
        password=creds['password']

        # print(remoteFilePath,localFilepath)

        ftp = FTP(server)  
        ftp.login(username, password)  
        with open(localFilepath, 'rb') as f:  
            ftp.storbinary('STOR %s' % remoteFilePath, f) 
        ftp.quit()

    return 0
