<?php
/************************************************************************************
 *
 *  Copyright (c) 2018 Thanasis Vergoulis & Konstantinos Zagganas &  Loukas Kavouras
 *  for the Information Management Systems Institute, "Athena" Research Center.
 *  
 *  This file is part of SCHeMa.
 *  
 *  SCHeMa is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *  
 *  SCHeMa is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *  
 *  You should have received a copy of the GNU General Public License
 *  along with Foobar.  If not, see <https://www.gnu.org/licenses/>.
 *
 ************************************************************************************/
use yii\helpers\Html;

$this->title='Short CWL difinition tutorial for software images for SCHeMa';

?>

<h1 class="headers"><?=Html::encode($this->title)?></h1>

<div class='row'><div class="col-md-12">An image definition CWL file is a <a href="https://en.wikipedia.org/wiki/YAML" target="_blank">YAML</a> containing objects. An object is a data structure equivalent to the "object" type in JSON, consisting of a unordered set of name/value pairs (referred to here as fields) and where the name is a string and the value is a string, number, boolean, array, or object.</div></div>

<div class='row'>&nbsp;</div>

<div class='row'><div class="col-md-12">A process is a basic unit of computation which accepts input data, performs some computation, and produces output data. Examples include CommandLineTools, Workflows, and ExpressionTools. In this tutorial we will only deal with CommandLineTools.</div></div>
<div class='row'>&nbsp;</div>
<div class='row'><div class="col-md-12">Each CWL file required by SCHeMa must optionally contain the following sections:
	<ul>
		<li>General information about the process,</li>
		<li>An input object is an object describing the inputs to an invocation of a process based on an input schema, which describes the valid format (required fields, data types) for an input object.</li>
		<li>An output object is an object describing the output resulting from an invocation of a process based on an output schema, which describes the valid format for an output object.</li>
	</ul>
Each component will be described in detail in the following sections.
</div></div>


<h2>General process information</h2>
<div class='row'><div class="col-md-12">An example of process information specification can be seen in the following example:</div></div>
<div class="code">
	cwlVersion: v1.0<br />
	class: CommandLineTool <br />
	baseCommand: /home/bufet/bufet.bin<br />
	hints:<br />
	&nbsp;DockerRequirement:<br />
	&nbsp;&nbsp;dockerPull: zagganas/bufet:latest<br />
</div>

<h3>cwlVersion</h3>
<div class='row'><div class="col-md-12">The cwlVersion field indicates the version of the CWL spec used by the document. Currently it is v1.0</div></div>

<h3>class</h3>
<div class='row'><div class="col-md-12">The class field indicates this document describes a command line tool.</div></div>

<h3>baseCommand</h3>
<div class='row'><div class="col-md-12">The baseCommand provides the name of program that will actually run inside the container.</div></div>

<h3>Retrieving image from an external repository (optional)</h3>
<div class='row'><div class="col-md-12">We need to specify some hints for how to find the image we want. In this case we list just our requirements for the docker image in DockerRequirements. The dockerPull: parameter takes the same value that you would pass to a docker pull command. That is, the name of the container image (you can even specify the tag, which is good idea for best practises when using containers for reproducible research).</div></div>

<h2>Input specification</h2>
The inputs of a tool is a list of input parameters that control how to run the tool. An example of an input object specification can be seen below: 
<div class="code">
inputs:<br />
&nbsp;miRNA-Gene interactions file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 1<br />
&nbsp;Output file name:<br />
&nbsp;&nbsp;type: string<br />
&nbsp;&nbsp;default: /data/output.txt<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 2<br />
&nbsp;miRNA query file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 3<br />
&nbsp;Ontology file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 4<br />
&nbsp;Number of random miRNA groups:<br />
&nbsp;&nbsp;type: int<br />
&nbsp;&nbsp;default: 1000000<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 5<br />
&nbsp;Number of threads:<br />
&nbsp;&nbsp;type: int<br />
&nbsp;&nbsp;default: 8<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 6<br />
&nbsp;&nbsp;&nbsp;prefix: -nt
</div>
Each parameter has an <i><strong>id</strong></i> which specifies the name of parameter, and <i><strong>type</strong></i> describing what types of values are valid for that parameter. Available types are string, int, long, float, double and file. Furthermore, for parameters other than file, the user can specify a default value by using the <i><strong>default</strong></i> field. The <i><strong>inputBinding</strong></i> field is used to provide more details about a certain input. More specifically, the <i><strong>position</strong></i> of the parameter (in what order it is passed to the script inside the container) is defined and additionally, the input requires a <i><strong>prefix</strong></i> (usually specified with a hyphen or a double hyphen) then the user can specify it.

<!-- <h2>Output specification</h2>
Currently output specification is ignored. It can either be ommitted or included like in the example below:
<div class="code">
outputs: []
</div> -->

<h2>Putting it all together</h2>
<div class="code">
cwlVersion: v1.0<br />
class: CommandLineTool <br />
baseCommand: /home/bufet/bufet.bin<br />
hints:<br />
&nbsp;DockerRequirement:<br />
&nbsp;&nbsp;dockerPull: zagganas/bufet:latest<br />
inputs:<br />
&nbsp;miRNA-Gene interactions file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 1<br />
&nbsp;Output file name:<br />
&nbsp;&nbsp;type: string<br />
&nbsp;&nbsp;default: /data/output.txt<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 2<br />
&nbsp;miRNA query file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 3<br />
&nbsp;Ontology file:<br />
&nbsp;&nbsp;type: file<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 4<br />
&nbsp;Number of random miRNA groups:<br />
&nbsp;&nbsp;type: int<br />
&nbsp;&nbsp;default: 1000000<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 5<br />
&nbsp;Number of threads:<br />
&nbsp;&nbsp;type: int<br />
&nbsp;&nbsp;default: 8<br />
&nbsp;&nbsp;inputBinding:<br />
&nbsp;&nbsp;&nbsp;position: 6<br />
&nbsp;&nbsp;&nbsp;prefix: -nt<br />
outputs: []
</div>
