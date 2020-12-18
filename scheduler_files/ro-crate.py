#!/usr/bin/python3
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



	


with open('/data/docker/RO-crates/arguments.json') as f:
  data = json.load(f)


# inputs=data['input_data']['0']
# print(data['input_data']['0'])


# print(data['software_description'])

wf_path = data['location']
files_list = []

Workflow.TYPES=["File", "SoftwareSourceCode"]

wf_crate = rocrate_api.make_workflow_rocrate(workflow_path=wf_path,wf_type="CWL",include_files=files_list)


wf_crate.isBasedOn = data['software_url']
wf_crate.name = data['software_name']
wf_crate.description=data['software_description']
wf_crate.creator=data['creator']

main_entity = entity.Entity(wf_crate, data['software_url'],
            properties={
                "@id": data['software_url'],
                "name": data['software_name'],
                "citation": data['publication'],  
                "@type": ["File", "SoftwareSourceCode"],
                "input": [ "{@id: #" + data['input_data']["%s"%x]['id']+"}" for x in data['field_names']],
                "output": [{"@id": "#" + data['output_data']['id']}],
                "version": data['version']
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

wf_crate.write_zip(data['ROCratesFolder']+'/'+ data['jobid'])
command=['chmod','777', data['ROCratesFolder']+'.zip']
subprocess.call(command)