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
