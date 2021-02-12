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

/**
 * View file for the About page 
 * 
 * @author: Kostis Zagganas
 * First version: Dec 2018
 */

use yii\helpers\Html;

$this->title = 'Privacy statement';
// $this->params['breadcrumbs'][] = $this->title;
?>
<div class="site-about">
    <h1><?= Html::encode($this->title) ?></h1>
    
	<h3>What Are Cookies?</h3>

	Cookies are small text files placed on your computer, phone, or other device when you visit websites and are used to record information about your activity, including the pages you view, the content you listen to, ads you click on, your settings, and other actions you take on the Service.

	Cookies can be “persistent” or “session” cookies. Persistent cookies stay on your device for a set period of time or until you delete them, while session cookies are deleted once you close your web browser.

	<h3>Use of Cookies</h3>

	Like most organisations, when you visit or interact with the Service, we and our service providers may use cookies for storing information about login sessions, to help provide, protect and improve the Service. In line with Article 5(3) of the ePrivacy Directive, consent is not required for technical storage or access of authentication cookies, for the duration of a session.

	<h3>Other Information we collect, store and record</h3>
	<ul>
    	<li><b>User execution history data:</b> we collect these data in order to keep track of resource consumption and to provide the user with statistics about their use of the platform.</li>
    </ul>
    


	<h3>Data Storage and Retention</h3>

	All data collected through our services, either through cookies or through forms and user input, are stored on our servers for the duration of the service provision.

	<h3>User Rights</h3>

	Users whose data we process in order to provide the necessary services have the following rights:
	<ol>
    	<li>The right to be informed regarding the objectives of processing, their lawful basis, the data retention period and who the data will be shared with. This information is included in this policy, however, the user may request additional information.</li>
    	<li>The right of access to the data we collect, process and retain.</li>
    	<li>The right to rectification to correct any inaccurate personal data we may hold.</li>
    	<li>The right to erasure to erase data after the data subject has stopped received our services.</li>
    	<li>The right to restrict processing in case this is beyond the scope of the processing objective or its lawful basis.</li>
    	<li>The right to object in case there is a basis for such a request.</li>
		</ol>

	The user may exercise her rights by emailing us at dpo@imis.athena-innovation.gr.

	ARC reserves the right to charge for the transaction costs of responding to the user’s requests.


	<h3>Privacy and Personal Data Settings</h3>

	<?=Html::a('Athena Research Centre','https://www.athena-innovation.gr/en/identity')?> is the Data controller for processing the data collected by you when you sign up and when you use our services, username. Data will be stored as long as the service is in use and they will not be used for other purposes nor given to third parties. Athena RC uses data security technologies to prevent any unauthorised access to data and guarantee data confidentiality. You have always the right to access, modify, erase and ask not to be the object of a decision based solely on automated processing including profiling. For this and for any further question you may have, please contact <?=Html::a('dpo@athena-innovation.gr','mailto:dpo@athena-innovation.gr')?>.

    
</div>
