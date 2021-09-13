<?php
/**
 * Extended Email functions.
 *
 *
 * @author	HAMAD ALI (ali sher)
 *
 */


/**
 * Decode mime data.
 *
 * @param	string	$data
 * @param	string	$encoding
 * @return	string
 */
function mail_mime_decode($data, $encoding) {
	switch($encoding) {
		case ENC8BIT:
			$data = imap_8bit($data);
			$data = quoted_printable_decode($data);
			break;
		case ENCBASE64:
			$data = base64_decode(stripslashes($data));
			break;
		case ENCQUOTEDPRINTABLE:
			$data = quoted_printable_decode($data);
			break;
	}
	return $data;
}


/**
 * Returns an array of alternative mail types.
 * 
 * @param	object	$mbox
 * @param	object	$parts
 * @param	string	$cur
 * @param	int	$msg_id
 * @return	mixed
 */ 
function mail_imap_locate_alternatives($mbox, $parts, $cur='', $msg_id) {
	if(strtoupper(@$parts->subtype) == 'ALTERNATIVE') {
		$temp = array();
	
		foreach($parts->parts as $k => $leaf) {
			if($cur == 0) {
				$cp = $k + 1;
			} else {
				$cp = $cur.'.'.(string)($k+1);
			}
			$temp[$leaf->subtype] = trim(mail_mime_decode(imap_fetchbody($mbox, $msg_id, $cp, FT_PEEK), $leaf->encoding));
			$sub_v = strrev($cp);
		} 

		return $temp;
		
	} elseif(strtoupper(@$parts->subtype) == 'PLAIN' || strtoupper(@$parts->subtype) == 'HTML') {
	
		$temp = array();
		$temp[$parts->subtype] = trim(mail_mime_decode(imap_fetchbody($mbox, $msg_id, $cur, FT_PEEK), $parts->encoding));
		return $temp;
		
	} elseif(is_array($parts)) {
	
		foreach($parts as $k => $part) {
			if($cur > 1) {
				$cur = (string)$cur . '.' . (string)($k+1);
			} else {
				$cur = 1;
			}
			return mail_imap_locate_alternatives($mbox, $part, $cur, $msg_id);
		}
		
	} else {
		
		if(is_array($parts->parts)) {
			if($cur == '') {
				$cur = 1;
			} else {
				$cur++;
			}
			
			return mail_imap_locate_alternatives($mbox, $parts->parts, $cur, $msg_id);
		}
		
	}
}


/**
 * Returns an email header unencoded as an array.
 *
 * @param	string	$headers
 * @param	mixed	$return_keys
 * @return	mixed
 */
function mail_header_to_array($headers, $return_keys=array()) {
	$list = array();

	$raw_headers = preg_replace("/\r\n[ \t]+/", ' ', $headers); // Unfold headers
	$raw_headers = explode("\r\n", $raw_headers);
	foreach($raw_headers as $value) {
		$name = strtoupper(substr($value, 0, $pos = strpos($value, ':')));

		if(in_array($name, $return_keys) || empty($return_keys)) {

			$value = ltrim(substr($value, $pos + 1));

			// Convert fields
			// @see http://bugs.php.net/bug.php?id=44098
			if(function_exists('iconv_mime_decode')) {
				$iconv_decoded = @iconv_mime_decode($value, 0, 'UTF-8');
				if($iconv_decoded == '') $iconv_decoded = imap_utf8($value);
				$value = utf8_decode($iconv_decoded);
			} else {
				$value = utf8_decode(imap_utf8($value));
			}
			
			$value = quoted_printable_decode($value);
			
			if($value != '') {
				if(strpos('iso-8859-1', $value)) {
					$value = html_entity_decode(utf8_decode($value));
				}
			}

			// Add to array
			if(isset($list[$name]) && is_array($list[$name])) {
				$list[$name][] = $value;
			} elseif (isset($list[$name])) {
				$list[$name] = array($list[$name], $value);
			} else {
				$list[$name] = $value;
			}
		}
	}
	
	return $list;
}


/**
 * Validates an email and returns true or false
 *
 * @param	string	$email
 * @param	bool	$extended_validation
 * @return	bool
 */
function mail_validate_email($email, $extended_validation=false) {
	$valid = false;
	
	if(function_exists('filter_var')) {
		if(filter_var($email, FILTER_VALIDATE_EMAIL)) {
			$valid = true;
		}
	} else {
		if(preg_match("^[\x20-\x2D\x2F-\x7E]+(\.[\x20-\x2D\x2F-\x7E]+)*@(([a-z0-9]([-a-z0-9]*[a-z0-9]+)?){1,63}\.)+[a-z0-9]{2,6}$/i", $email)) {
			$valid = true;
		}
	}
	
	/*
	Check the domain name after the @ is a real domain name.
	(We do this by checking if an MX record exists for that domain name
	and then we check if port 25 is open for that domain name,
	which makes sure that the domain name is in use.)
	*/
	if($valid && $extended_validation) {
		list($Username, $Domain) = split("@", $email);
		if(getmxrr($Domain, $MXHost)) {
			$valid = true;
		} else {
			if(fsockopen($Domain, 25, $errno, $errstr, 30)) {
				$valid = true;
			} else {
				$valid = false;
			}
		}
	}
	
	return $valid;
}

?>