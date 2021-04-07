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
from rocrate.model.workflow import Workflow
from rocrate.model.file import File
from rocrate.model import entity
from rocrate.model import contextentity
import rocrate.rocrate as roc


argsFile=sys.argv[1]


with open(argsFile) as f:
  data = json.load(f)




wf_path = data['location']
files_list = []

Workflow.TYPES=["File", "SoftwareSourceCode"]

wf_crate = rocrate_api.make_workflow_rocrate(workflow_path=wf_path,wf_type="CWL",include_files=files_list)


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
                "citation": data['publication'],  
                "@type": ["File", "SoftwareSourceCode"],
                "input": [ "{@id: #" + data['input_data']["%s"%x]['id']+"}" for x in data['field_names']],
                "output": [{"@id": "#" + data['output_data']['id']}],
                "version": data['software_version']
                },
                 )

 
wf_crate._add_context_entity(main_entity)


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
    wf_crate._add_context_entity(input_entity)

output_entity = entity.Entity(wf_crate, '', 
        properties={"@id": "#" + data['output_data']['id'],
                    "@type": 'FormalParameter',
                    'url': data['output_data']['data'],
     })
wf_crate._add_context_entity(output_entity)

outFile=data['ROCratesFolder']+'/'+ data['jobid']
wf_crate.write_zip(outFile)
command=['chmod','777', outFile +'.zip']
subprocess.call(command)