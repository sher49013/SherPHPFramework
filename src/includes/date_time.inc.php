<?php

/**
 * Function to calculate date or time difference.
 * 
 * Function to calculate date or time difference. Returns an array
 *
 * @param	int	$time1
 * @param	int	$time2
 * @return       array
 */
function get_time_difference( $time1, $time2 ) {
	$time1 = (int)$time1;
	$time2 = (int)$time2;
	$result = array(
		'days' => 0,
		'hours' => 0,
		'minutes' => 0,
		'seconds' => 0
	);
	if( $time1 == 0 || $time2 == 0) return $result;
	
	if($time2 > $time1) {
		$start = date("H:i",$time1);
		$end = date("H:i",$time2);
	} else {
		$start = date("H:i",$time2);
		$end = date("H:i",$time1);	
	}	
	
	$uts['start']      =    $time2;
	$uts['end']        =    $time1;
	if( $uts['start']!==-1 && $uts['end']!==-1 ) {
		if( $uts['end'] >= $uts['start'] ) {
		
			$diff    =    $uts['end'] - $uts['start'];
			if( $days=intval((floor($diff/86400))) ) $diff = $diff % 86400;
			if( $hours=intval((floor($diff/3600))) ) $diff = $diff % 3600;
			if( $minutes=intval((floor($diff/60))) ) $diff = $diff % 60;
			$diff    =    intval( $diff );    
			if($days > 0) $hours += $days*24;
			return( array('days'=>str_pad($days,2,0, STR_PAD_LEFT), 'hours'=>str_pad($hours,2,0, STR_PAD_LEFT), 'minutes'=>str_pad($minutes,2,0, STR_PAD_LEFT), 'seconds'=>str_pad($diff,2,0, STR_PAD_LEFT)) );
		}	
	}	
	return $result;
}


/**
 * function for time formatting.as x days ago
 *
 * @param	string	$format
 *
 * @return string
 */
function formattime_ago($time) {
	$period = $GLOBALS['i18']['time_period'];
	$periods = $GLOBALS['i18']['time_periods'];
	$lengths = array("60","60","24","7","4.35","12","10");

	$now = time();
	$difference = $now - $time;
	$tense = $GLOBALS['i18']['ago'];

	for($j = 0; $difference >= $lengths[$j] && $j < count($lengths)-1; $j++) {
		$difference /= $lengths[$j];
	}
	$difference = round($difference);
	
	if($difference > 1)
		$period_lbl = $periods[$j];
	else
		$period_lbl = $period[$j];
		
	return str_replace('%', $difference." ".$period_lbl, $tense);
}

/**
 * function for date formatting.
 *
 * @param	string	$format
 *
 * @return string
 */
function format_date($value) {	
	$value = (int)$value;
	if($value > 0)
		return date($GLOBALS['i18']['date_format'],$value);
	else
		return '';
}

/**
 * Function that returns number of days or months
 * between two timestamps.
 *
 * @param	int	$time1
 * @param	int	$time2
 * @param	string	$what
 * @return	float
 */
function date_diff_between_ts($time1, $time2, $what='d') {
	$result = 0;

	if($time2 > $time1) {
		$nseconds = $time2 - $time1;
	} else {
		$nseconds = $time1 - $time2;
	}

	if($what == 'd') {		// Days
		$result = (float)($nseconds / 86400);
	} elseif($what == 'w') {	// Weeks
		$result = (float)($nseconds / 604800);
	} elseif($what == 'm') {	// Months
		if($time1 > $time2) {
			$temp = $time1;
			$time1 = $time2;
			$time2 = $temp;
		}

		$day1 = uwa_date('j', $time1);
		$mon1 = uwa_date('n', $time1);
		$year1 = uwa_date('Y', $time1);
		$day2 = uwa_date('j', $time2);
		$mon2 = uwa_date('n', $time2);
		$year2 = uwa_date('Y', $time2);

		if($day1 > $day2) {
			$result = (($year2 - $year1) * 12) + ($mon2 - $mon1 - 1);
		} else {
			$result = (($year2 - $year1) * 12) + ($mon2 - $mon1);
		}
	} elseif($what == 'y') {	// Years
		$weeks = (float)($nseconds / 604800);
		$result = $weeks / 52;
	} elseif($what == 'h') {	// Hours
		$result = (float)($nseconds / 3600);
	} elseif($what == 'i') {	// Minutes
		$result = (int)($nseconds / 60);
	}

	return $result;
}


/**
 * function for datetime formatting.
 *
 * @param	string	$format
 *
 * @return string
 */
function format_datetime($value) {
	$value = (int)$value;
	if($value > 0 )
		return date($GLOBALS['i18']['formats']['date_time'],$value);
	else
		return '';
}


/**
 * Return all user selectable timezones.
 * 
 * @return	mixed
 */
function date_get_timezones() {
	return array('Africa/Abidjan', 'Africa/Accra', 'Africa/Algiers', 'Africa/Asmera', 'Africa/Bamako', 'Africa/Bangui', 'Africa/Banjul', 'Africa/Bissau', 'Africa/Blantyre', 'Africa/Brazzaville', 'Africa/Bujumbura', 'Africa/Cairo', 'Africa/Casablanca', 'Africa/Ceuta', 'Africa/Conakry', 'Africa/Dakar', 'Africa/Djibouti', 'Africa/Douala', 'Africa/Freetown', 'Africa/Gaborone', 'Africa/Harare', 'Africa/Johannesburg', 'Africa/Kampala', 'Africa/Khartoum', 'Africa/Kigali', 'Africa/Kinshasa', 'Africa/Lagos', 'Africa/Libreville', 'Africa/Lome', 'Africa/Luanda', 'Africa/Lubumbashi', 'Africa/Lusaka', 'Africa/Malabo', 'Africa/Maputo', 'Africa/Maseru', 'Africa/Mbabane', 'Africa/Mogadishu', 'Africa/Monrovia', 'Africa/Nairobi', 'Africa/Ndjamena', 'Africa/Niamey', 'Africa/Nouakchott', 'Africa/Ouagadougou', 'Africa/Porto-Novo', 'Africa/Tripoli', 'Africa/Tunis', 'Africa/Windhoek', 'America/Adak', 'America/Anchorage', 'America/Anguilla', 'America/Antigua', 'America/Araguaina', 'America/Aruba', 'America/Asuncion', 'America/Barbados', 'America/Belem', 'America/Belize', 'America/Bogota', 'America/Boise', 'America/Cancun', 'America/Caracas', 'America/Cayenne', 'America/Cayman', 'America/Chicago', 'America/Chihuahua', 'America/Cuiaba', 'America/Curacao', 'America/Danmarkshavn', 'America/Dawson', 'America/Denver', 'America/Detroit', 'America/Dominica', 'America/Edmonton', 'America/Eirunepe', 'America/Fortaleza', 'America/Godthab', 'America/Grenada', 'America/Guadeloupe', 'America/Guatemala', 'America/Guayaquil', 'America/Guyana', 'America/Halifax', 'America/Havana', 'America/Hermosillo', 'America/Indiana/Indianapolis', 'America/Indiana/Knox', 'America/Indiana/Marengo', 'America/Indiana/Vevay', 'America/Inuvik', 'America/Iqaluit', 'America/Jamaica', 'America/Juneau', 'America/Kentucky/Louisville', 'America/Kentucky/Monticello', 'America/Lima', 'America/Maceio', 'America/Managua', 'America/Manaus', 'America/Martinique', 'America/Mazatlan', 'America/Menominee', 'America/Merida', 'America/Miquelon', 'America/Monterrey', 'America/Montevideo', 'America/Montreal', 'America/Montserrat', 'America/Nassau', 'America/Nipigon', 'America/Nome', 'America/Noronha', 'America/Panama', 'America/Pangnirtung', 'America/Paramaribo', 'America/Phoenix', 'America/Port-au-Prince', 'America/Recife', 'America/Regina', 'America/Santiago', 'America/Scoresbysund', 'America/Tegucigalpa', 'America/Thule', 'America/Tijuana', 'America/Tortola', 'America/Vancouver', 'America/Whitehorse', 'America/Winnipeg', 'America/Yakutat', 'America/Yellowknife', 'Antarctica/Casey', 'Antarctica/Davis', 'Antarctica/DumontDUrville', 'Antarctica/Mawson', 'Antarctica/McMurdo', 'Antarctica/Palmer', 'Antarctica/Syowa', 'Antarctica/Vostok', 'Asia/Aden', 'Asia/Almaty', 'Asia/Amman', 'Asia/Anadyr', 'Asia/Aqtau', 'Asia/Aqtobe', 'Asia/Ashgabat', 'Asia/Baghdad', 'Asia/Bahrain', 'Asia/Baku', 'Asia/Bangkok', 'Asia/Beirut', 'Asia/Bishkek', 'Asia/Brunei', 'Asia/Calcutta', 'Asia/Choibalsan', 'Asia/Chongqing', 'Asia/Colombo', 'Asia/Damascus', 'Asia/Dhaka', 'Asia/Dili', 'Asia/Dubai', 'Asia/Dushanbe', 'Asia/Gaza', 'Asia/Harbin', 'Asia/Hovd', 'Asia/Irkutsk', 'Asia/Jakarta', 'Asia/Jayapura', 'Asia/Jerusalem', 'Asia/Kabul', 'Asia/Kamchatka', 'Asia/Karachi', 'Asia/Kashgar', 'Asia/Katmandu', 'Asia/Krasnoyarsk', 'Asia/Kuching', 'Asia/Kuwait', 'Asia/Magadan', 'Asia/Manila', 'Asia/Muscat', 'Asia/Nicosia', 'Asia/Novosibirsk', 'Asia/Omsk', 'Asia/Pontianak', 'Asia/Pyongyang', 'Asia/Qatar', 'Asia/Rangoon', 'Asia/Riyadh', 'Asia/Saigon', 'Asia/Sakhalin', 'Asia/Samarkand', 'Asia/Seoul', 'Asia/Shanghai', 'Asia/Singapore', 'Asia/Taipei', 'Asia/Tashkent', 'Asia/Tbilisi', 'Asia/Tehran', 'Asia/Thimphu', 'Asia/Tokyo', 'Asia/Ulaanbaatar', 'Asia/Urumqi', 'Asia/Vientiane', 'Asia/Vladivostok', 'Asia/Yakutsk', 'Asia/Yekaterinburg', 'Asia/Yerevan', 'Atlantic/Azores', 'Atlantic/Bermuda', 'Atlantic/Canary', 'Atlantic/Faeroe', 'Atlantic/Madeira', 'Atlantic/Reykjavik', 'Atlantic/Stanley', 'Australia/Adelaide', 'Australia/Brisbane', 'Australia/Darwin', 'Australia/Hobart', 'Australia/Lindeman', 'Australia/Melbourne', 'Australia/Perth', 'Australia/Sydney', 'CST6CDT', 'EST', 'EST5EDT', 'Europe/Amsterdam', 'Europe/Andorra', 'Europe/Athens', 'Europe/Belgrade', 'Europe/Berlin', 'Europe/Brussels', 'Europe/Bucharest', 'Europe/Budapest', 'Europe/Chisinau', 'Europe/Copenhagen', 'Europe/Dublin', 'Europe/Gibraltar', 'Europe/Helsinki', 'Europe/Istanbul', 'Europe/Kaliningrad', 'Europe/Kiev', 'Europe/Lisbon', 'Europe/London', 'Europe/Luxembourg', 'Europe/Madrid', 'Europe/Malta', 'Europe/Minsk', 'Europe/Monaco', 'Europe/Moscow', 'Europe/Oslo', 'Europe/Paris', 'Europe/Prague', 'Europe/Riga', 'Europe/Rome', 'Europe/Samara', 'Europe/Simferopol', 'Europe/Sofia', 'Europe/Stockholm', 'Europe/Tallinn', 'Europe/Tirane', 'Europe/Uzhgorod', 'Europe/Vaduz', 'Europe/Vienna', 'Europe/Vilnius', 'Europe/Warsaw', 'Europe/Zaporozhye', 'Europe/Zurich', 'HST', 'Indian/Antananarivo', 'Indian/Chagos', 'Indian/Christmas', 'Indian/Cocos', 'Indian/Comoro', 'Indian/Kerguelen', 'Indian/Mahe', 'Indian/Maldives', 'Indian/Mauritius', 'Indian/Mayotte', 'Indian/Reunion', 'MST', 'MST7MDT', 'PST8PDT', 'Pacific/Apia', 'Pacific/Auckland', 'Pacific/Chatham', 'Pacific/Easter', 'Pacific/Efate', 'Pacific/Enderbury', 'Pacific/Fakaofo', 'Pacific/Fiji', 'Pacific/Funafuti', 'Pacific/Galapagos', 'Pacific/Gambier', 'Pacific/Guadalcanal', 'Pacific/Guam', 'Pacific/Honolulu', 'Pacific/Johnston', 'Pacific/Kiritimati', 'Pacific/Kosrae', 'Pacific/Kwajalein', 'Pacific/Majuro', 'Pacific/Marquesas', 'Pacific/Midway', 'Pacific/Nauru', 'Pacific/Niue', 'Pacific/Norfolk', 'Pacific/Noumea', 'Pacific/Palau', 'Pacific/Pitcairn', 'Pacific/Ponape', 'Pacific/Rarotonga', 'Pacific/Saipan', 'Pacific/Tahiti', 'Pacific/Tarawa', 'Pacific/Tongatapu', 'Pacific/Truk', 'Pacific/Wake', 'Pacific/Wallis');
}




/**
 * Returns current system time.
 *
 * @return	int
 */
if(!function_exists('date_time_get_time')) {
	function date_time_get_time() {
		return time();
	}
}


/**
 * Wrapper function for mktime.
 *
 * @param	int	$hour
 * @param	int	$minute
 * @param	int	$second
 * @param	int	$month
 * @param	int	$day
 * @param	int	$year
 *
 * @return int
 */
function uwa_mktime($hour, $minute, $second, $month, $day, $year) {
	if(function_exists('adodb_mktime')) {	// We can have problems with mktime when using dates before 1970 or hight 2038
		$func_name = 'adodb_mktime';
	} else {
		$func_name = 'mktime';
	}
	return $func_name($hour, $minute, $second, $month, $day, $year);
}


/**
 * Wrapper function for date.
 *
 * @param	string	$format
 * @param	int	$timestamp
 *
 * @return string
 */
function uwa_date($format, $timestamp=false) {
	if(!$timestamp) $timestamp = date_time_get_time();
	
	if(function_exists('adodb_date')) {	// We can have problems with mktime when using dates before 1970 or hight 2038
		$func_name = 'adodb_date';
	} else {
		$func_name = 'date';
	}
	return $func_name($format, $timestamp);
}


/**
 * Wrapper function for gmdate.
 *
 * @param	string	$format
 * @param	int	$timestamp
 *
 * @return string
 */
function uwa_gmdate($format, $timestamp=false) {
	if(!$timestamp) $timestamp = date_time_get_time();
	
	if(function_exists('adodb_gmdate')) {	// We can have problems with mktime when using dates before 1970 or hight 2038
		$func_name = 'adodb_gmdate';
	} else {
		$func_name = 'gmdate';
	}
	return $func_name($format, $timestamp);
}


/**
 * Wrapper function for getdate.
 *
 * @param	int	$timestamp
 *
 * @return string
 */
function uwa_getdate($timestamp=false) {
	if(!$timestamp) $timestamp = date_time_get_time();
	
	if(function_exists('adodb_getdate')) {	// We can have problems with mktime when using dates before 1970 or hight 2038
		$func_name = 'adodb_getdate';
	} else {
		$func_name = 'getdate';
	}
	return $func_name($timestamp);
}


/**
 * Returns a timestamp from user's timezone
 * as timestamp for system's timezone, mostly UTC.
 *
 * @param	int		$timestamp
 * @param	string		$local_tz
 * @return	int
 */
function date_local_to_sys($timestamp, $local_tz='') {
	if($local_tz == '') $local_tz = @$_SESSION['cms']['user']['timezone'];
	if($local_tz == '') return $timestamp;

	$offset = date_tz_offset($timestamp, $local_tz);
	return $timestamp - $offset;
}


/**
 * Returns a timestamp from system's timezone
 * as timestamp for user's timezone..
 *
 * @param	int		$timestamp
 * @param	string		$local_tz
 * @return	int
 */
function date_sys_to_local($timestamp, $local_tz='') {
	if($local_tz == '') $local_tz = @$_SESSION['cms']['user']['timezone'];
	if($local_tz == '') return $timestamp;
	
	$offset = date_tz_offset($timestamp, $local_tz);
	return $timestamp + $offset;
}


/**
 * Returns offset between two timezones for a
 * given timestamp.
 *
 * @param	int		$timestamp
 * @param	string		$from_tz
 * @param	string		$to_tz
 * @return	int
 */
function date_tz_offset($timestamp, $from_tz, $to_tz='') {
	if($to_tz == '') $to_tz = $GLOBALS['env']['timezone'];

	if(function_exists('date_offset_get')) {
		$from_dtz = new DateTimeZone($from_tz);
		$to_dtz = new DateTimeZone($to_tz);
		$from_dt = new DateTime(app_date('Y-m-d H:i', $timestamp), $from_dtz);
		$to_dt = new DateTime('now', $to_dtz);
		$offset = $from_dtz->getOffset($from_dt) - $to_dtz->getOffset($to_dt);
	} else { // PHP version 4
		$timezone_offsets = array (
			'-43200' => ' Etc/GMT+12',
			'-39600' => ' Etc/GMT+11, MIT, Pacific/Apia, Pacific/Midway, Pacific/Niue, Pacific/Pago Pago, Pacific/Samoa, US/Samoa',
			'-36000' => ' -America/Adak, -America/Atka, Etc/GMT+10, HST, Pacific/Fakaofo, Pacific/Honolulu, Pacific/Johnston, Pacific/Rarotonga, Pacific/Tahiti, SystemV/HST10, -US/Aleutian, US/Hawaii',
			'-34200' => ' Pacific/Marquesas',
			'-32400' => ' -AST, -America/Anchorage, -America/Juneau, -America/Nome, -America/Yakutat, Etc/GMT+9, Pacific/Gambier, SystemV/YST9, -SystemV/YST9YDT, -US/Alaska',
			'-28800' => ' -America/Dawson, -America/Ensenada, -America/Los Angeles, -America/Tijuana, -America/Vancouver, -America/Whitehorse, -Canada/Pacific, -Canada/Yukon, Etc/GMT+8, -Mexico/BajaNorte, -PST, -PST8PDT, Pacific/Pitcairn, SystemV/PST8, -SystemV/PST8PDT, -US/Pacific, -US/Pacific-New',
			'-25200' => ' -America/Boise, -America/Cambridge Bay, -America/Chihuahua, America/Dawson Creek, -America/Denver, -America/Edmonton, America/Hermosillo, -America/Inuvik, -America/Mazatlan, America/Phoenix, -America/Shiprock, -America/Yellowknife, -Canada/Mountain, Etc/GMT+7, -MST, -MST7MDT, -Mexico/BajaSur, -Navajo, PNT, SystemV/MST7, -SystemV/MST7MDT, US/Arizona, -US/Mountain',
			'-21600' => ' America/Belize, -America/Cancun, -America/Chicago, America/Costa Rica, America/El Salvador, America/Guatemala, America/Managua, -America/Menominee, -America/Merida, America/Mexico City, -America/Monterrey, -America/North Dakota/Center, -America/Rainy River, -America/Rankin Inlet, America/Regina, America/Swift Current, America/Tegucigalpa, -America/Winnipeg, -CST, -CST6CDT, -Canada/Central, Canada/East-Saskatchewan, Canada/Saskatchewan, -Chile/EasterIsland, Etc/GMT+6, Mexico/General, -Pacific/Easter, Pacific/Galapagos, SystemV/CST6, -SystemV/CST6CDT, -US/Central',
			'-18000' => ' America/Bogota, America/Cayman, -America/Detroit, America/Eirunepe, America/Fort Wayne, -America/Grand Turk, America/Guayaquil, -America/Havana, America/Indiana/Indianapolis, America/Indiana/Knox, America/Indiana/Marengo, America/Indiana/Vevay, America/Indianapolis, -America/Iqaluit, America/Jamaica, -America/Kentucky/Louisville, -America/Kentucky/Monticello, America/Knox IN, America/Lima, -America/Louisville, -America/Montreal, -America/Nassau, -America/New York, -America/Nipigon, America/Panama, -America/Pangnirtung, America/Port-au-Prince, America/Porto Acre, America/Rio Branco, -America/Thunder Bay, Brazil/Acre, -Canada/Eastern, -Cuba, -EST, -EST5EDT, Etc/GMT+5, IET, Jamaica, SystemV/EST5, -SystemV/EST5EDT, US/East-Indiana, -US/Eastern, US/Indiana-Starke, -US/Michigan',
			'-14400' => ' America/Anguilla, America/Antigua, America/Aruba, -America/Asuncion, America/Barbados, America/Boa Vista, America/Caracas, -America/Cuiaba, America/Curacao, America/Dominica, -America/Glace Bay, -America/Goose Bay, America/Grenada, America/Guadeloupe, America/Guyana, -America/Halifax, America/La Paz, America/Manaus, America/Martinique, America/Montserrat, America/Port of Spain, America/Porto Velho, America/Puerto Rico, -America/Santiago, America/Santo Domingo, America/St Kitts, America/St Lucia, America/St Thomas, America/St Vincent, America/Thule, America/Tortola, America/Virgin, -Antarctica/Palmer, -Atlantic/Bermuda, -Atlantic/Stanley, Brazil/West, -Canada/Atlantic, -Chile/Continental, Etc/GMT+4, PRT, SystemV/AST4, -SystemV/AST4ADT',
			'-12600' => ' -America/St Johns, -CNT, -Canada/Newfoundland',
			'-10800' => ' AGT, -America/Araguaina, America/Belem, America/Buenos Aires, America/Catamarca, America/Cayenne, America/Cordoba, -America/Fortaleza, -America/Godthab, America/Jujuy, -America/Maceio, America/Mendoza, -America/Miquelon, America/Montevideo, America/Paramaribo, -America/Recife, America/Rosario, -America/Sao Paulo, -BET, -Brazil/East, Etc/GMT+3',
			 '-7200' => ' America/Noronha, Atlantic/South Georgia, Brazil/DeNoronha, Etc/GMT+2',
			 '-3600' => ' -America/Scoresbysund, -Atlantic/Azores, Atlantic/Cape Verde, Etc/GMT+1',
			     '0' => ' Africa/Abidjan, Africa/Accra, Africa/Bamako, Africa/Banjul, Africa/Bissau, Africa/Casablanca, Africa/Conakry, Africa/Dakar, Africa/El Aaiun, Africa/Freetown, Africa/Lome, Africa/Monrovia, Africa/Nouakchott, Africa/Ouagadougou, Africa/Sao Tome, Africa/Timbuktu, America/Danmarkshavn, -Atlantic/Canary, -Atlantic/Faeroe, -Atlantic/Madeira, Atlantic/Reykjavik, Atlantic/St Helena, -Eire, Etc/GMT, Etc/GMT+0, Etc/GMT-0, Etc/GMT0, Etc/Greenwich, Etc/UCT, Etc/UTC, Etc/Universal, Etc/Zulu, -Europe/Belfast, -Europe/Dublin, -Europe/Lisbon, -Europe/London, -GB, -GB-Eire, GMT, GMT0, Greenwich, Iceland, -Portugal, UCT, UTC, Universal, -WET, Zulu',
			  '3600' => ' Africa/Algiers, Africa/Bangui, Africa/Brazzaville, -Africa/Ceuta, Africa/Douala, Africa/Kinshasa, Africa/Lagos, Africa/Libreville, Africa/Luanda, Africa/Malabo, Africa/Ndjamena, Africa/Niamey, Africa/Porto-Novo, Africa/Tunis, -Africa/Windhoek, -Arctic/Longyearbyen, -Atlantic/Jan Mayen, -CET, -ECT, Etc/GMT-1, -Europe/Amsterdam, -Europe/Andorra, -Europe/Belgrade, -Europe/Berlin, -Europe/Bratislava, -Europe/Brussels, -Europe/Budapest, -Europe/Copenhagen, -Europe/Gibraltar, -Europe/Ljubljana, -Europe/Luxembourg, -Europe/Madrid, -Europe/Malta, -Europe/Monaco, -Europe/Oslo, -Europe/Paris, -Europe/Prague, -Europe/Rome, -Europe/San Marino, -Europe/Sarajevo, -Europe/Skopje, -Europe/Stockholm, -Europe/Tirane, -Europe/Vaduz, -Europe/Vatican, -Europe/Vienna, -Europe/Warsaw, -Europe/Zagreb, -Europe/Zurich, -MET, -Poland',
			  '7200' => ' -ART, Africa/Blantyre, Africa/Bujumbura, -Africa/Cairo, Africa/Gaborone, Africa/Harare, Africa/Johannesburg, Africa/Kigali, Africa/Lubumbashi, Africa/Lusaka, Africa/Maputo, Africa/Maseru, Africa/Mbabane, Africa/Tripoli, -Asia/Amman, -Asia/Beirut, -Asia/Damascus, -Asia/Gaza, -Asia/Istanbul, -Asia/Jerusalem, -Asia/Nicosia, -Asia/Tel Aviv, CAT, -EET, -Egypt, Etc/GMT-2, -Europe/Athens, -Europe/Bucharest, -Europe/Chisinau, -Europe/Helsinki, -Europe/Istanbul, -Europe/Kaliningrad, -Europe/Kiev, -Europe/Minsk, -Europe/Nicosia, -Europe/Riga, -Europe/Simferopol, -Europe/Sofia, Europe/Tallinn, -Europe/Tiraspol, -Europe/Uzhgorod, Europe/Vilnius, -Europe/Zaporozhye, -Israel, Libya, -Turkey',
			 '10800' => ' Africa/Addis Ababa, Africa/Asmera, Africa/Dar es Salaam, Africa/Djibouti, Africa/Kampala, Africa/Khartoum, Africa/Mogadishu, Africa/Nairobi, Antarctica/Syowa, Asia/Aden, -Asia/Baghdad, Asia/Bahrain, Asia/Kuwait, Asia/Qatar, Asia/Riyadh, EAT, Etc/GMT-3, -Europe/Moscow, Indian/Antananarivo, Indian/Comoro, Indian/Mayotte, -W-SU',
			 '11224' => ' Asia/Riyadh87, Asia/Riyadh88, Asia/Riyadh89, Mideast/Riyadh87, Mideast/Riyadh88, Mideast/Riyadh89',
			 '12600' => ' -Asia/Tehran, -Iran',
			 '14400' => ' -Asia/Aqtau, -Asia/Baku, Asia/Dubai, Asia/Muscat, -Asia/Tbilisi, -Asia/Yerevan, Etc/GMT-4, -Europe/Samara, Indian/Mahe, Indian/Mauritius, Indian/Reunion, -NET',
			 '16200' => ' Asia/Kabul',
			 '18000' => ' -Asia/Aqtobe, Asia/Ashgabat, Asia/Ashkhabad, -Asia/Bishkek, Asia/Dushanbe, Asia/Karachi, Asia/Samarkand, Asia/Tashkent, -Asia/Yekaterinburg, Etc/GMT-5, Indian/Kerguelen, Indian/Maldives, PLT',
			 '19800' => ' Asia/Calcutta, IST',
			 '20700' => ' Asia/Katmandu',
			 '21600' => ' Antarctica/Mawson, Antarctica/Vostok, -Asia/Almaty, Asia/Colombo, Asia/Dacca, Asia/Dhaka, -Asia/Novosibirsk, -Asia/Omsk, Asia/Thimbu, Asia/Thimphu, BST, Etc/GMT-6, Indian/Chagos',
			 '23400' => ' Asia/Rangoon, Indian/Cocos',
			 '25200' => ' Antarctica/Davis, Asia/Bangkok, Asia/Hovd, Asia/Jakarta, -Asia/Krasnoyarsk, Asia/Phnom Penh, Asia/Pontianak, Asia/Saigon, Asia/Vientiane, Etc/GMT-7, Indian/Christmas, VST',
			 '28800' => ' Antarctica/Casey, Asia/Brunei, Asia/Chongqing, Asia/Chungking, Asia/Harbin, Asia/Hong Kong, -Asia/Irkutsk, Asia/Kashgar, Asia/Kuala Lumpur, Asia/Kuching, Asia/Macao, Asia/Manila, Asia/Shanghai, Asia/Singapore, Asia/Taipei, Asia/Ujung Pandang, Asia/Ulaanbaatar, Asia/Ulan Bator, Asia/Urumqi, Australia/Perth, Australia/West, CTT, Etc/GMT-8, Hongkong, PRC, Singapore',
			 '32400' => ' Asia/Choibalsan, Asia/Dili, Asia/Jayapura, Asia/Pyongyang, Asia/Seoul, Asia/Tokyo, -Asia/Yakutsk, Etc/GMT-9, JST, Japan, Pacific/Palau, ROK',
			 '34200' => ' ACT, -Australia/Adelaide, -Australia/Broken Hill, Australia/Darwin, Australia/North, -Australia/South, -Australia/Yancowinna',
			 '36000' => ' -AET, Antarctica/DumontDUrville, -Asia/Sakhalin, -Asia/Vladivostok, -Australia/ACT, Australia/Brisbane, -Australia/Canberra, -Australia/Hobart, Australia/Lindeman, -Australia/Melbourne, -Australia/NSW, Australia/Queensland, -Australia/Sydney, -Australia/Tasmania, -Australia/Victoria, Etc/GMT-10, Pacific/Guam, Pacific/Port Moresby, Pacific/Saipan, Pacific/Truk, Pacific/Yap',
			 '37800' => ' -Australia/LHI, -Australia/Lord Howe',
			 '39600' => ' -Asia/Magadan, Etc/GMT-11, Pacific/Efate, Pacific/Guadalcanal, Pacific/Kosrae, Pacific/Noumea, Pacific/Ponape, SST',
			 '41400' => ' Pacific/Norfolk',
			 '43200' => ' -Antarctica/McMurdo, -Antarctica/South Pole, -Asia/Anadyr, -Asia/Kamchatka, Etc/GMT-12, Kwajalein, -NST, -NZ, -Pacific/Auckland, Pacific/Fiji, Pacific/Funafuti, Pacific/Kwajalein, Pacific/Majuro, Pacific/Nauru, Pacific/Tarawa, Pacific/Wake, Pacific/Wallis',
			 '45900' => ' -NZ-CHAT, -Pacific/Chatham',
			 '46800' => ' Etc/GMT-13, Pacific/Enderbury, Pacific/Tongatapu',
			 '50400' => ' Etc/GMT-14, Pacific/Kiritimati, null, localize/timezone'
		);

		$from_offset = 0;
		$to_offset = 0;
		foreach($timezone_offsets as $offset => $zones) {

			if($from_tz != '') {
				if(strpos(strtolower($zones), strtolower($from_tz)) !== false) $from_offset = $offset;
			}

			if($to_tz != '') {
				if(strpos(strtolower($zones), strtolower($to_tz)) !== false) $to_offset = $offset;
			}
		}

		$old_tz = @ini_get('date.timezone');
		if($old_tz == '') $old_tz = $GLOBALS['env']['timezone'];
		@ini_set('date.timezone', $from_tz);
		@putenv('TZ='.$from_tz);

		if(app_date('I', $timestamp)) $from_offset += 3600;

		@ini_set('date.timezone', $old_tz);
		@putenv('TZ='.$old_tz);

		$offset = ($from_offset - $to_offset);
	}

	return $offset;
}


/**
 * Converts a date into a timestamp.
 * 
 * @param	string		$value
 * @param	int		$language_uid
 * @param	string		$format_key
 * @param	string		$format_override
 * @return	string
 */
function date_to_ts($value) {
	return strtotime($value);
}


/**
 * Output a date.
 *
 * Either use user's language settings
 * or load a language from db.
 *
 * @param	int	$timestamp
 * @param	int	$language_uid
 * @param	string	$format_key
 * @return	string
 */
function date_get_date($timestamp) {
    return app_date($GLOBALS['i18']['formats']['date'], $timestamp);
}


/**
 * Output a date with time.
 *
 * Either use user's language settings
 * or load a language from db.
 *
 * @param	int	$timestamp
 * @param	int	$language_uid
 * @param	string	$format_key
 * @return	string
 */
function date_get_date_time($timestamp) {
    return app_date($GLOBALS['i18']['formats']['date_time'], $timestamp);
}

/**
 * Returns current system time.
 *
 * @return	int
 */
if(!function_exists('date_time_get_time')) {
	function date_time_get_time() {
		return time();
	}
}


/**
 * Wrapper function for date.
 *
 * @param	string	$format
 * @param	int	$timestamp
 *
 * @return string
 */
function app_date($format, $timestamp=false) {
	if(!$timestamp) $timestamp = date_time_get_time();
	
	if(function_exists('adodb_date')) {	// We can have problems with mktime when using dates before 1970 or hight 2038
		$func_name = 'adodb_date';
	} else {
		$func_name = 'date';
	}
	return $func_name($format, $timestamp);
}
?>