import subprocess
import os
import requests

def restore():
	folder='/data/www/schema/scheduler_files/'
	ontFolder=folder + 'ontology/'
	fusekiLock=ontFolder + '/fuseki.lock'
	ontology=ontFolder+'elixir_gr.owl'
	ontologyInf=ontFolder + 'elixir_inferred.owl'
	backupFolder=ontFolder + 'older/'
	sput=ontFolder+ 's-put'

	backups=os.listdir(backupFolder)
	backups=sorted(backups, key=lambda x: os.path.getctime(backupFolder + x), reverse=True)
	subprocess.call(['cp', backupFolder + backups[0], ontology])
	subprocess.call(['java','-jar',ontFolder + 'inferenceOnly.jar', ontology, ontologyInf])

	sparql="""
            DELETE
            WHERE
            {
              	?x ?y ?z
            }
            """

	response = requests.post('http://localhost:3030/elixir-gr/update',
   		data={'update': sparql})

	subprocess.call([sput,'http://localhost:3030/elixir-gr/', 'default', ontologyInf])

	subprocess.call(['rm', fusekiLock])

