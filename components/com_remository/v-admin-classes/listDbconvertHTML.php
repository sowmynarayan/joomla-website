<?php

/**************************************************************
* This file is part of Remository
* Copyright (c) 2006 Martin Brampton
* Issued as open source under GNU/GPL
* For support and other information, visit http://remository.com
* To contact Martin Brampton, write to martin@remository.com
*
* Remository started life as the psx-dude script by psx-dude@psx-dude.net
* It was enhanced by Matt Smith up to version 2.10
* Since then development has been primarily by Martin Brampton,
* with contributions from other people gratefully accepted
*/

class listDbconvertHTML extends remositoryAdminHTML {

	function view () {
		echo <<<CONFIRM_DBCONVERT

		<table class="adminheading">
			<tr>
				<th>Convert pre-3.20 Database</th>
			</tr>
        </table>
		<p>
		If you have Remository tables from a pre-3.20 version, this option
		will convert them to the 3.20 structure.
		</p>
		<p>
		RUNNING THIS WILL DELETE ALL DATA FROM ANY 3.20+ TABLES!
		</p>
		<p>
		This can be run as many times as necessary to recreate the version
		3.20 tables, but remember that every time it is run, the 3.20 tables
		are emptied.  Any work done in the 3.20+ Remository WILL BE LOST.
		DO NOT GO FURTHER UNLESS YOU ARE SURE!
		</p>
		<form action="index2.php" method="post" name="adminForm">
		<div>
			<input type="hidden" name="confirm" value="confirm" />
			<input type="submit" value="Convert" />
			<input type="hidden" name="task" value=""/>
			<input type="hidden" name="option" value="com_remository"/>
			<input type="hidden" name="repnum" value="$this->repnum" />
			<input type="hidden" name="act" value="dbconvert"/>
		</div>
		</form>

CONFIRM_DBCONVERT;

	}
}

?>