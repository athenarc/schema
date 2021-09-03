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
import json
import sys
import subprocess
import atexit
import os
import tempfile
from contextlib import redirect_stdout
from pathlib import Path


from rocrate import rocrate_api
# from rocrate.model.workflow import Workflow
# from rocrate.model.file import File
from rocrate.model import entity
from rocrate.model import contextentity
import rocrate.rocrate as roc


argsFile=sys.argv[1]


with open(argsFile) as f:
  data = json.load(f)




wf_path = data['location']
files_list = []

# Workflow.TYPES=["File", "SoftwareSourceCode"]


#The commented command below created an additional html preview file in previous versions. In the current version
#the preview file is not created, therefore we call the commands below to include it.

# wf_crate = rocrate_api.make_workflow_rocrate(workflow_path=wf_path,wf_type="CWL",include_files=files_list)


cwl=None
wf_crate = roc.ROCrate(gen_preview=True)
workflow_path = Path(wf_path)
wf_file = wf_crate.add_workflow(
    str(workflow_path), workflow_path.name, fetch_remote=False,
    main=True, lang="CWL", gen_cwl=(cwl is None)
)

# if the source is a remote URL then add https://schema.org/codeRepository
# property to it this can be checked by checking if the source is a URL
# instead of a local path
if 'url' in wf_file.properties():
    wf_file['codeRepository'] = wf_file['url']

# add extra files
for file_entry in files_list:
    wf_crate.add_file(file_entry)


wf_crate.isBasedOn = data['software_url']
wf_crate.name = data['software_name']
wf_crate.description=data['software_description']
wf_crate.creator=data['creator']

if (data['image'] is not None):
    wf_crate.image = data['image']

main_entity = entity.Entity(wf_crate, data['software_url'],
            properties={
                "@id": data['software_url'],
                "name": data['software_name'],
                "experiment_description": data['experiment_description'],
                "citation": data['publication'],  
                "@type": ["File", "SoftwareSourceCode"],
                "input": [ "{@id: #" + data['input_data']["%s"%x]['id']+"}" for x in data['field_names']],
                "output": [{"@id": "#" + data['output_data']['id']}],
                "version": data['software_version']
                },
                 )

 
wf_crate.add(main_entity)


for x in data['field_names']:
    if (data['input_data']["%s"%x]['type']=='File'):
        input_entity = entity.Entity(wf_crate, '', 
        properties={"@id": "#"+data['input_data']["%s"%x]['id'],
                    "@type": 'FormalParameter',
                    'name': data['input_data']["%s"%x]['name'],
                    'url': data['input_data']["%s"%x]['url'],
     })
    else:
        input_entity = entity.Entity(wf_crate, '', 
        properties={"@id": "#"+data['input_data']["%s"%x]['id'],
                    "@type": 'FormalParameter',
                    'name': data['input_data']["%s"%x]['name'],
     })
    wf_crate.add(input_entity)

output_entity = entity.Entity(wf_crate, '', 
        properties={"@id": "#" + data['output_data']['id'],
                    "@type": 'FormalParameter',
                    'url': data['output_data']['data'],
     })
wf_crate.add(output_entity)

outFile=data['ROCratesFolder']+'/'+ data['jobid']
wf_crate.write_zip(outFile)
command=['chmod','777', outFile +'.zip']
subprocess.call(command)