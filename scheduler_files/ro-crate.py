#!/usr/bin/python3
from rocrate.model.workflow import Workflow
import rocrate.rocrate as roc
import sys
import subprocess
import atexit
import os
import tempfile
from contextlib import redirect_stdout
from pathlib import Path

import rocrate.rocrate as roc
from rocrate.model import entity
from rocrate.model.workflow import Workflow
from galaxy2cwl import get_cwl_interface

softName=sys.argv[1]
softVersion=sys.argv[2]
softDescription=sys.argv[3]
softUrl=sys.argv[4]
inputData=sys.argv[5]
outpoutData=sys.argv[6]
publication=sys.argv[7]
outPath=sys.argv[8]
location=sys.argv[9]
uploader=sys.argv[10]
jobId=sys.argv[11]



from rocrate import rocrate_api
from rocrate.model.workflow import Workflow

wf_path = location
files_list = []



wf_crate = rocrate_api.make_workflow_rocrate(workflow_path=wf_path,wf_type="CWL",include_files=files_list)

TYPES = ["File", "SoftwareSourceCode", "MPI"]



wf_crate.isBasedOn = softUrl
wf_crate.name = softName
wf_crate.description=softDescription
uploader = wf_crate.add_person('001', {'name': uploader})
wf_crate.creator=uploader
# wf_crate.image=

# wf_crate.license = 'MIT'
# wf_crate.keywords = ['GTN', 'climate']
# wf_crate.CreativeWorkStatus = "Stable"
# wf_crate.publisher = publisher
# wf_crate.creator = [ creator, publisher ]


wf_crate.write_zip(outPath+'/'+ jobId)

command=['chmod','777', outPath+'.zip']
subprocess.call(command)