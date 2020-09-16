import requests
import os.path
import restoreFusekiBackup as bkp
import time

def classify(name,version):

  	#todo cjeck for fuseki lock
	folder='/data/www/schema/scheduler_files/'
	fusekiLock=folder + 'ontology/fuseki.lock'
	locked=os.path.exists(fusekiLock)
	i=0
	while locked:
		i+=1
		locked=os.path.exists(fusekiLock)
		time.sleep(1)
		if i>20:
			bkp.restore()
			break


	classifierTags=set(['CPUIntensive', 'MemoryIntensive', 'GPUProgram', 'DiskIntensive', 'Parallel'])
	sparql="""
              PREFIX ont: <http://purl.org/net/ns/ontology-annot#>
              PREFIX onto: <http://www.ontotext.com/>
              prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
              prefix ontology:<http://www.elixir-gr.org#>
              prefix xsd: <http://www.w3.org/2001/XMLSchema#>
              
              SELECT ?z 
              
              where {
              	ontology:""" + name + '-' + version + """ rdf:type ?z .
              	filter(strstarts(str(?z),str(ontology:)))
              } 
       		"""
	# sparql2="""
 #              PREFIX ont: <http://purl.org/net/ns/ontology-annot#>
 #              PREFIX onto: <http://www.ontotext.com/>
 #              prefix rdf: <http://www.w3.org/1999/02/22-rdf-syntax-ns#>
 #              prefix ontology:<http://www.elixir-gr.org#>
 #              prefix xsd: <http://www.w3.org/2001/XMLSchema#>
              
 #              SELECT ?z 
              
 #              where {
 #              	ontology:""" + name + """ ontology:hasNumberofCpuCores ?z
 #              } 
 #       	"""

	response = requests.post('http://localhost:3030/elixir-gr/query',
			data={'query': sparql})

	results=(response.json())['results']['bindings']
	tagList=set()
	for result in results:
		uri=result['z']['value'].split("#")
		tag=uri[1]
		if tag in classifierTags:
			if tag!='Parallel':
				tagList.add(tag)
		# else:
		# 	response2=requests.post('http://localhost:3030/elixir-gr/query',
		# 			data={'query': sparql2})
		# 	coresNum=(response2.json())['results']['bindings'][0]['z']['value']
		# 	tagList.add((tag,coresNum))

	return tagList


def decideServerPool(tagList):
    ###uncomment the following for gpu and disk-intensive stuff
    # if ("GPUProgram" in tagList):
    # 	return "gpu"
    # if ("DiskIntensive" in taglist):
    # 	return "disk"

    if "MemoryIntensive" in tagList:
      return "medium-node"
    
    return "thin-node"


