<?php
/*	Project:	EQdkp-Plus
 *	Package:	EQdkp-plus
 *	Link:		http://eqdkp-plus.eu
 *
 *	Copyright (C) 2006-2016 EQdkp-Plus Developer Team
 *
 *	This program is free software: you can redistribute it and/or modify
 *	it under the terms of the GNU Affero General Public License as published
 *	by the Free Software Foundation, either version 3 of the License, or
 *	(at your option) any later version.
 *
 *	This program is distributed in the hope that it will be useful,
 *	but WITHOUT ANY WARRANTY; without even the implied warranty of
 *	MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *	GNU Affero General Public License for more details.
 *
 *	You should have received a copy of the GNU Affero General Public License
 *	along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

if ( !defined('EQDKP_INC') ){
	header('HTTP/1.0 404 Not Found');exit;
}

$module_lang = array(
	'attendance'	=> 'Raids (%d Tage)',
	'lifetime'		=> 'Raids (Lebenszeit)',
	'attendance_fromto' => 'Raidteilnahme',
);

$preset_lang = array(
	'attendance_30'	=> 'Raidteilnahme (30 Tage)',
	'attendance_60'	=> 'Raidteilnahme (60 Tage)',
	'attendance_90'	=> 'Raidteilnahme (90 Tage)',
	'attendance_30_real'	=> 'Raidteilnahme (30 Tage, von Lebenszeit abh.)',
	'attendance_60_real'	=> 'Raidteilnahme (60 Tage, von Lebenszeit abh.)',
	'attendance_90_real'	=> 'Raidteilnahme (90 Tage, von Lebenszeit abh.)',
	'attendance_lt'	=> 'Raidteilnahme (Lebenszeit)',
	'attendance_fromto_all' => 'Raidteilnahme (def. Zeitraum)',
);
?>