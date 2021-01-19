#!/usr/bin/python3
import subprocess
import os.path
import re

def getInputs(data,folder,config):
    allowedTypes=set(['http','ftp'])
    mounts=set()
    referenceMounts=set()

    try:
        inputs=data['inputs']
    except:
        return 1,set(),set()
    try:
        outputs=data['outputs']
    except:
        return 2,set(),set()

    for inpt in inputs:
        try:
            url=inpt['url']
        except:
            return 3,set(),set()
        
        try:
            path=inpt['path']
        except:
            return 4,set(),set()

        path=path.split('/')
        mpath='/'.join(path[:-1])
        folderName=path[-2]
        localFilename=path[-1]

        localFolder=folder + '/' + folderName
        localFilepath=localFolder + '/' + localFilename

        if not os.path.exists(localFolder):
            subprocess.call(['mkdir','-p',localFolder])

        urlTok=url.split(':')
        protocol=urlTok[0]
        try:
            remoteFilename=url.split('/')[-1]
        except:
            return 9,set(),set()

        if protocol not in allowedTypes:
            return 5,set(),set()
        
        if protocol=='http':
            returnCode=subprocess.call(['wget', '-O', localFilepath, url])
            if returnCode!=0:
                return 8,set(),set()

        elif protocol=='ftp':
            patt=re.compile("ftp://([^/ ]+)/.+")

            try:
                server=patt.match(url).groups()[0]
            except:
                return 6,set(),set()

            try:
                creds=config['ftp-creds'][server]
            except:
                return 7,set(),set()
            username=creds['username']
            password=creds['password']

            commUser='--ftp-user=' + username
            commPass="--ftp-password=" + password

            returnCode=subprocess.call(['wget', '-O',localFilepath,commUser,commPass,url])
            if returnCode!=0:
                return 8,set(),set()

        mount=(localFolder,mpath)
        mounts.add(mount)
        subprocess.call(['chmod','777',localFolder,'-R'])

    for outpt in outputs:
        try:
            url=outpt['url']
        except:
            return 10,set(),set()
        
        try:
            path=outpt['path']
        except:
            return 11,set(),set()

        path=path.split('/')
        mpath='/'.join(path[:-1])
        folderName=path[-2]
        localFilename=path[-1]

        localFolder=folder + '/' + folderName
        localFilepath=localFolder + '/' + localFilename

        if not os.path.exists(localFolder):
            subprocess.call(['mkdir','-p',localFolder])

        mount=(localFolder,mpath)
        mounts.add(mount)
        subprocess.call(['chmod','777',localFolder,'-R'])
    
    reference=[]
    try:
        reference=data['reference_data']
    except:
        pass

    for ref in reference:
        try:
            path=ref['path']
        except:
            return 12,set(),set()
        try:
           cpath=ref['container_path']
        except:
            return 13,set(),set()

        referenceMounts.add((path,cpath))
    return 0, list(mounts), list(referenceMounts)








