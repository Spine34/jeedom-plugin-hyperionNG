<?php

/* This file is part of Jeedom.
*
* Jeedom is free software: you can redistribute it and/or modify
* it under the terms of the GNU General Public License as published by
* the Free Software Foundation, either version 3 of the License, or
* (at your option) any later version.
*
* Jeedom is distributed in the hope that it will be useful,
* but WITHOUT ANY WARRANTY; without even the implied warranty of
* MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
* GNU General Public License for more details.
*
* You should have received a copy of the GNU General Public License
* along with Jeedom. If not, see <http://www.gnu.org/licenses/>.
*/

require_once __DIR__  . '/../../../../core/php/core.inc.php';
/*
 *
 * Fichier d’inclusion si vous avez plusieurs fichiers de class ou 3rdParty à inclure
 * 
 */

function rgb2hex($r, $g, $b)
{
	$r = dechex($r);
	if (strlen($r) == 1) {
		$r = '0' . $r;
	}
	$g = dechex($g);
	if (strlen($g) == 1) {
		$g = '0' . $g;
	}
	$b = dechex($b);
	if (strlen($b) == 1) {
		$b = '0' . $b;
	}
	$hex = '#' . $r . $g . $b;
	return $hex;
}
