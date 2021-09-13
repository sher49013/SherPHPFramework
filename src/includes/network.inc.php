<?php
/**
 * Utilities for network operations.
 *
 * @author	HAMAD ALI (ali sher)
 */


/**
 * Sends default http headers.
 *
 * @see		http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 * @see		http://msdn.microsoft.com/workshop/author/perf/perftips.asp
 * @see		http://www.badpenguin.org/docs/php-cache.html
 *
 * @see		http://www.google.de/intl/de/webmasters/guidelines.html
 *
 * @used	includes/message_box.inc.php
 * @used	includes/graphic_manager.inc.php
 * @used	Backend and frontend process controller.
 * @used	Backend Debugger debug.php
 *
 * @param	string	$content_type
 * @param	int	Optional $status_code
 * @param	int	Optional $last_modified
 * @param	int	Optional $expires
 * @param	int	Optional $cache_time	Set negative value if there should not be sent any header.
 * @param	mixed	Optional $extra fields.
 * @return	void
 */
function network_send_http_headers($content_type, $status_code=0, $last_modified=0, $expires=0, $cache_time=0, $extra=array()) {

	// Status code set?
	if($status_code != 0) header(network_http_status($status_code));


	// Last Modified
	if($last_modified == 0) {
		$last_modified = time() - 10;
	}
	header('Last-Modified: '.network_get_header_date($last_modified));


	// ETag Header
	header('ETag: '.md5($last_modified));


	// Expires
	header('Expires: '.network_get_header_date(time() + $expires));


	// We could handle HTTP_IF_MODIFIED_SINCE and HTTP_IF_MODIFIED_SINCE here
	// but this is not useful for backend functionality, so it is directly implemented
	// whereever it is needed. (In CMS request handler for example)


	// Caching
	// * max-age=seconds - the number of seconds from the time of the request you wish this object to be keep into the cache;
	// * s-maxage=seconds - like max-age but it only applies to proxy;
	// * public - tell to handle the content has cacheable even if it would normally be uncacheable, it is used for example for authenticated pages;
	// * no-cache - force both proxy and browser to validate the document before to provide a cached copy;
	// * must-revalidate - tell the browser to obey to any information you give them about a webpage;
	// * proxy-revalidate - like must-revalidate but applies to proxy;
	if($cache_time == 0) {
		if(@$extra['download'] == true && $GLOBALS['env']['is_ssl']) {
			header('Pragma: public');
		} else {
			header('Pragma: no-cache');
		}
		header('Cache-Control: max-age=1, s-maxage=1, no-store, no-cache, post-check=0, pre-check=0, must-revalidate, proxy-revalidate');
	} elseif($cache_time > 0) {
		header('Pragma: private');	// Allows the user's browser to cache the file, but public proxies shouldn't.
		header('Cache-Control: max-age='.$cache_time.', s-maxage='.$cache_time.', pre-check='.$cache_time);
	}


	// Set encoding and content type
	if(@$extra['download'] != true) {
		switch(strtolower($content_type)) {
			case 'html':
				/**
				 * Deprecated:
				 *
				 * header('Connection: close');
				 * header('Keep-Alive: timeout=15, max=50');
				 *
				 * @see http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html#sec14.10
				 * @see	http://www.abakus-internet-marketing.de/foren/viewtopic/t-2974.html
				 * @see	http://de.selfhtml.org/html/xhtml/unterschiede.htm
				 */
				header('Accept-Ranges: bytes');
				header('Content-Type: text/html; charset='.$GLOBALS['config']['cms']['output_charset']);		// Content-Type: application/xhtml+xml
				break;

			case 'xhtml':
				header('Accept-Ranges: bytes');
				header('Content-Type: application/xhtml+xml; charset='.$GLOBALS['config']['cms']['output_charset']);		// Content-Type: application/xhtml+xml
				break;

			case 'wml':
				header('Accept-Ranges: bytes');
				header('Content-Type: text/vnd.wap.wml; charset='.$GLOBALS['config']['cms']['output_charset']);		// Content-Type: application/xhtml+xml
				break;

			case 'xml':
				header('Accept-Ranges: bytes');
				header('Content-Type: text/xml; charset='.$GLOBALS['config']['cms']['output_charset']);
				break;

			case 'json':
				header('Content-Type: application/json; charset='.$GLOBALS['config']['cms']['output_charset']);
				break;

			case 'rss':
				header('Accept-Ranges: bytes');
				header('Content-Type: application/rss+xml; charset='.$GLOBALS['config']['cms']['output_charset']);
				break;

			case 'ldif':
				header('Accept-Ranges: bytes');
				header('Content-Type: text/ldif; charset='.$GLOBALS['config']['cms']['output_charset']);
				break;

			case 'syncml':
				header('application/vnd.syncml+xml');
				break;

			case 'jpeg':
				header('Content-Type: image/jpeg');
				header('Content-Disposition: inline');
				break;

			case 'jpg':
				header('Content-Type: image/jpeg');
				header('Content-Disposition: inline');
				break;

			case 'png':
				header('Content-Type: image/png');
				header('Content-Disposition: inline');
				break;

			case 'gif':
				header('Content-Type: image/gif');
				header('Content-Disposition: inline');
				break;

			default:
				header('Content-Type: '.$content_type);
				break;
		}

	} else {
		if(@$extra['filename'] != '') {
			$binary = true; // Could be used later, currently all is binary

			$user_agent = strtolower(network_get_client_user_agent());
			if(strpos($user_agent, 'safari') !== false) {
				header('Content-Transfer-Encoding: Binary');
				header('Connection: close');

				$ctype = 'application/force-download';
			} elseif(strpos($user_agent, 'msie 6.0') !== false) {
				$ctype = 'application/octet-stream';
			} else {
				$ctype = network_get_content_type_by_ext(io_get_file_extension($extra['filename']));
			}

			header('Content-Type: '.$ctype);
			header('Content-Disposition: attachment; filename="'.$extra['filename'].'"');

			if($binary == true) header('Content-Transfer-Encoding: binary');
			if(@$extra['filesize'] != '') header('Content-Length: '.$extra['filesize']);
		}
	}
}


/**
 * Get content type by file extension.
 *
 * @param	string	$ext
 * @return	string
 */
function network_get_content_type_by_ext($ext) {
	$ctype = '';

	switch($ext) {
		case 'pdf':	$ctype='application/pdf';		break;
		case 'exe':	$ctype='application/octet-stream';	break;
		case 'zip':	$ctype='application/zip';		break;
		case 'rtf':	$ctype='text/rtf';			break;
		case 'doc':	$ctype='application/msword';		break;
		case 'dot':	$ctype='application/msword';		break;
		case 'xls':	$ctype='application/vnd.ms-excel'; 	break;
		case 'xla':	$ctype='application/vnd.ms-excel'; 	break;
		case 'ppt':	$ctype='application/vnd.ms-powerpoint';	break;
		case 'csv':	$ctype='text/comma-separated-values';	break;
		case 'jpeg':	$ctype='image/jpeg';			break;
		case 'jpg':	$ctype='image/jpeg';			break;
		case 'gif':	$ctype='image/gif';			break;
		case 'png':	$ctype='image/png';			break;
		case 'tif':	$ctype='image/tiff';			break;
		case 'tiff':	$ctype='image/tiff';			break;
		case 'mp3':	$ctype='audio/mpeg';			break;
		case 'wav':	$ctype='audio/x-wav';			break;
		case 'mpeg':	$ctype='video/mpeg';			break;
		case 'mpg':	$ctype='video/mpeg';			break;
		case 'mpe':	$ctype='video/mpeg';			break;
		case 'mov':	$ctype='video/quicktime';		break;
		case 'avi': 	$ctype='video/x-msvideo';		break;
		case 'flv': 	$ctype='video/x-flv';			break;
		case 'swf':	$ctype='application/x-shockwave-flash';	break;
		case 'xml':	$ctype='text/xml'; $binary = false;	break;
		case 'txt':	$ctype='text/plain'; $binary = false;	break;
		default:	$ctype='application/force-download';
	}

	return $ctype;
}


/**
 * Send a local file for download.
 *
 * When using readfile() with very large files, it's possible to run into problems due to the memory_limit setting;
 * apparently readfile() pulls the whole file into memory at once.
 *
 * One solution is to make sure memory_limit is larger than the largest file you'll use with readfile().  A better solution is to write a chunking readfile.  Here's a simple one that doesn't exactly conform to the API, but is close enough for most purposes:
 *
 * @see		http://php.net/manual/de/function.readfile.php
 *
 * @param	string	$filename
 * @return	bool
 */
function network_readfile_chunked($filename) {
	@set_time_limit(0);
	$chunksize = 1 * (1024 * 1024); // Set how many bytes per chunk
	$handle = fopen($filename, 'rb');
	if ($handle === false) return false;
	while(!feof($handle)) {
		$buffer = fread($handle, $chunksize);
		echo $buffer;
	}
	return fclose($handle);
}


/**
 * Redirect to another page.
 *
 * @see		http://de2.php.net/session_write_close
 *
 * @param	string	$url
 * @param	bool	$permanently=false
 * @param	bool	$temporarily=false
 * @return	void
 */
function network_redirect($url, $permanently=false, $temporarily=false) {
	session_write_close();

	if(isset($GLOBALS['log'])) $GLOBALS['log']->debug('Redirecting to "'.$url.'"');

	if($permanently == true) header('HTTP/1.1 301 Moved Permanently');
	if($temporarily == true) header('HTTP/1.1 302 Moved Temporarily');

	header('Cache-Control: no-cache, must-revalidate');
	header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');

	header('Location: '.$url);
	die;
}


/**
 * Returns the script's URL.
 *
 * @link	http://sniptools.com/tipstricks/php_self-getenv-request_uri-or-script_name
 * @param	bool	$with_parameters: Script URL with parameters or without ?
 * @param	bool	$with_server: Return http://... ?
 * @return	string
 */
function network_get_script_url($with_parameters = true, $with_server = false) {
	$script = isset($_SERVER['HTTP_X_REWRITE_URL']) ? $_SERVER['HTTP_X_REWRITE_URL'] : $_SERVER['REQUEST_URI']; // mod_rewrite for IIS adds original request URL to special $_SERVER var

	if($with_parameters == true) {
		if(empty($script)) $script = $_SERVER['PHP_SELF'].'?'.$_SERVER['QUERY_STRING'];
		if(empty($script)) $script = $_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING'];
	} else {
		if($script != '') {
			$pos = strpos($script, '?');
			if($pos !== false) {
				$script = substr($script, 0, $pos);
			}
		} else {
			$script = $_SERVER['PHP_SELF'];
		}
		if(empty($script)) $script = array_pop(explode('/',$_SERVER['SCRIPT_NAME']));
	}

	if($with_server == true) {
		$script = $GLOBALS['config']['cms']['site_url'].$script;
	}

	return $script;
}


/**
 * Returns a date in default header format.
 *
 * @param	int	$date
 * @return	string
 */
function network_get_header_date($date) {
	return date('D, d M Y H:i:s', $date).' GMT';
}


/**
 * Checks whether two IPs are from the same class C subnet.
 *
 * @param	string	$ip1
 * @param	string	$ip2
 * @return	bool
 */
function network_equal_subnet($ip1, $ip2) {
	$parts1 = explode(".", $ip1);
	$parts2 = explode(".", $ip2);

	for($i = 0; $i < 3; $i++) {
		if($parts1[$i] != $parts2[$i]) {
			return false;
		}
	}

	return true;
}


/**
 * Extracts client's IP address for various HTTP headers.
 *
 * @return	string
 */
function network_get_client_ip() {
	if(isset($_SERVER['REMOTE_ADDR'])) {
		return $_SERVER['REMOTE_ADDR'];
	} elseif(isset($_SERVER['HTTP_FROM'])) {
		return $_SERVER['HTTP_FROM'];
	} elseif(isset($_SERVER['HTTP_CLIENT_IP'])) {
		return $_SERVER['HTTP_CLIENT_IP'];
	} else {
		return network_get_client_internal_ip();
	}
}


/**
 * Validates a IP address.
 *
 * @param	string	$ip
 * @return	bool
 */
function network_is_valid_ip($ip) {
	return preg_match("/^([1-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])(\.([0-9]|[1-9][0-9]|1[0-9][0-9]|2[0-4][0-9]|25[0-5])){3}$/", $ip);
}


/**
 * Returns client's IP behind proxy.
 *
 * @return	string
 */
function network_get_client_internal_ip() {
	if(isset($_SERVER['HTTP_X_FORWARDED_FOR']) AND preg_match_all('#\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}#s', $_SERVER['HTTP_X_FORWARDED_FOR'], $matches))  {

		// check for internal IPs by looking at the first octet
		if(is_array($matches)) {
			foreach($matches[0] AS $ip) {
				if(preg_match("#(127|10|172\.16|192\.168)\.#", $ip)) {
					return $ip;
				}
			}
		}
	}

	return '';
}


/**
 * Returns client's user agent.
 *
 * @return	string
 */
function network_get_client_user_agent() {
	return isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
}


/**
 * Returns client's http referer.
 *
 * @return	string
 */
function network_get_client_referer() {
	return isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '';
}


/**
 * Returns the answer for a http request.
 *
 * Use CURL if available, otherwise use plain fopen function.
 *
 * @see		http://www.hudzilla.org/php/15_10_2.php
 * @see		http://de2.php.net/manual/de/ref.curl.php
 *
 * @param	string	$url
 * @param	mixed	$additional_headers
 * @param	mixed	$post_vars
 * @param	bool	$with_header
 * @param	int		$timeout
 * @param	string	$method
 * @return	string
 */
function network_http_request($url, $additional_headers=array(), $post_vars=array(), $with_header=true, $timeout=180, $method='') {
	$content = '';
	$GLOBALS['last_network_code'] = 0;

	// Try cURL
	if(function_exists('curl_init')) {

		$isFile = false;
		if(is_array($post_vars)) {
            foreach ($post_vars as $item) {
                if (substr($item, 0, 1) == '@') {
                    $isFile = true;
                    break;
                }
            }
        }


		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($ch, CURLOPT_VERBOSE, false);
		curl_setopt($ch, CURLOPT_HEADER, $with_header);
		curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
		curl_setopt($ch, CURLOPT_HTTPHEADER, $additional_headers);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		if($isFile) curl_setopt($ch, CURLOPT_SAFE_UPLOAD, false);
		if(isset($additional_headers['User-Agent'])) curl_setopt($ch, CURLOPT_USERAGENT, $additional_headers['User-Agent']);

		if(!empty($post_vars)) {
		    curl_setopt($ch, CURLOPT_POST, 1);
		    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_vars);
		}

		if(!empty($method)) {
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
        }

		if(@$GLOBALS['config']['network']['proxy_host'] != '') curl_setopt($ch, CURLOPT_PROXY, $GLOBALS['config']['network']['proxy_host']);
		if(@$GLOBALS['config']['network']['proxy_port'] != '') curl_setopt($ch, CURLOPT_PROXYPORT, $GLOBALS['config']['network']['proxy_port']);
		if(@$GLOBALS['config']['network']['proxy_auth'] != '') curl_setopt($ch, CURLOPT_PROXYUSERPWD, $GLOBALS['config']['network']['proxy_auth']);

		$content = curl_exec($ch);
		$GLOBALS['last_network_code'] = curl_getinfo($ch, CURLINFO_HTTP_CODE);

		if($with_header) {
			$content = str_replace("HTTP/1.0 200 Connection established\r\n\r\n", '', $content);
		}

		if(curl_errno($ch)) {
			$GLOBALS['log']->error('Curl error: ' . curl_error($ch).' - '.print_r($post_vars, true));
		}

		curl_close($ch);
	} else {	// Plain
		if(@$GLOBALS['config']['network']['proxy_host'] != '') {
			$GLOBALS['log']->error('Proxy connections are only supported using CURL.');

			message_box('Configuration issue', 'Proxy connections are only supported when using PHP CURL. Please activate CURL in your php environment.');
		}

		$url_parsed = @parse_url($url);
		$host = @$url_parsed['host'];
		if(isset($url_parsed['port'])) {
			$port = $url_parsed['port'];
		} else {
			$port = 80;
		}
		$path = @$url_parsed["path"];


		// If url is http://example.com without final /
		if(empty($path)) $path='/';


		// Query parameters
		if(isset($url_parsed['query'])) $path .= '?'.$url_parsed['query'];


		// Start request
		$errno = 0;
		$errstr = '';

		if(!empty($post_vars)) {
			$out = "POST $path HTTP/1.1\r\nHost: $host\r\n";

			$post_data = http_build_query($post_vars);
			$out .= 'Content-Type: application/x-www-form-urlencoded; charset=utf-8'."\r\n";
			$out .= 'Content-Length: '.strlen($post_data)."\r\n";
			$out .= 'Connection: Close'."\r\n\r\n";
			$out .= $post_data;
		} else {
			$out = "GET $path HTTP/1.0\r\nHost: $host\r\n";
		}

		foreach($additional_headers as $key => $value) {
			$out .= $key.": ".$value."\r\n";
		}
		$out .= "\r\n";

		$fp = @fsockopen($host, $port, $errno, $errstr, 30);
		if(!$fp) {
			$GLOBALS['log']->info('HTTP-Connection to '.$host.' failed.');
			return '';
		}
		fwrite($fp, $out);


		// Read server answer
		while(!feof($fp)) {
			$content .= fgets($fp, 128);
		}
		fclose($fp);
	}

	return $content;
}


/**
 * Adds parameters to a given URL.
 *
 * @param	string	$url
 * @param	string	$parameter
 *
 * @return	string
 */
function network_url_add_parameter($url, $parameter) {
	$result = '';

	if(strpos($url, '?') === false) {
		$result = $url.'?';
	} else {
		if(substr($url, -1) != '&') {
			$result = $url.'&';
		} else {
			$result = $url;
		}
	}

	$arr = explode('&', $parameter);

	for($i = 0; $i < count($arr); $i++) {
		list($key, $value) = explode('=', $arr[$i]);

		$pos = strpos($result, '&'.$key.'=');
		if($pos === false) $pos = strpos($result, '?'.$key.'=');

		if($pos !== false) {
			$pos++;
			$val_pos = strpos($result, '&', $pos + 1);

			if($val_pos === false) $val_pos = strlen($result);

			$result = substr($result, 0, $pos).$key.'='.$value.substr($result, $val_pos);
		} else {
			$result .= $key.'='.$value;
		}

		if($i < count($arr) - 1) $result .= '&';
	}

	if(substr($result, -1) == '&') $result = substr($result, 0, strlen($result) - 1);

	return $result;
}


/**
 * Remove a parameter from an URL.
 *
 * @param	string	$url
 * @param	string	$key
 * @param	string	$value
 * @return	string
 */
function network_url_remove_param($url, $key) {
	$pos = strpos($url, '?');
	if($pos === false) {
		$prefix = '';
		$s_params = $url;
	} else {
		$prefix = substr($url, 0, $pos + 1);
		$s_params = substr($url, $pos + 1);
	}

	$params = explode('&', $s_params);
	foreach($params as $index => $value) {
		$pos = strpos($value, '=');
		if($pos !== false) {
			$k = substr($value, 0, $pos);
			if($k == $key) unset($params[$index]);
		}
	}

	$result = $prefix.implode('&', $params);
	if(substr($result, -1) == '&') $result = substr($result, 0, strlen($result) - 1);

	return $result;
}


/**
 * Replace a parameter in an URL.
 *
 * @param	string	$url
 * @param	string	$key
 * @param	string	$value
 * @param	bool	$insert
 * @return	string
 */
function network_url_replace_param($url, $key, $value, $insert=true) {
	$pos = strpos($url, '?');
	if($pos === false) {
		$prefix = '';
		$s_params = $url;
	} else {
		$prefix = substr($url, 0, $pos + 1);
		$s_params = substr($url, $pos + 1);
	}

	$found = false;
	if(strpos($s_params, '&') !== false) {
		$params = explode('&', $s_params);
		foreach($params as $index => $param) {
			$pos = strpos($param, '=');
			if($pos !== false) {
				$k = substr($param, 0, $pos);
				if($k == $key) {
					$params[$index] = $key.'='.$value;
					$found = true;
				}
			}
		}
	}

	if($found == false && $insert == true) {
		$params = array();
		$params[] = $key.'='.$value;
		if(strpos($url, '?') === false) {
			$result = $url.'?'.implode('&', $params);
		} else {
			$result = $url.'&'.implode('&', $params);
		}
	} else {
		$result = $prefix.implode('&', $params);
	}

	if(substr($result, -1) == '&') $result = substr($result, 0, strlen($result) - 1);
	return $result;
}


/**
 * HTTP Protocol defined status codes.
 *
 * @param	int	$status
 * @return	string
 */
function network_http_status($status) {
	$http = array (
		100 => "HTTP/1.1 100 Continue",
		101 => "HTTP/1.1 101 Switching Protocols",
		200 => "HTTP/1.1 200 OK",
		201 => "HTTP/1.1 201 Created",
		202 => "HTTP/1.1 202 Accepted",
		203 => "HTTP/1.1 203 Non-Authoritative Information",
		204 => "HTTP/1.1 204 No Content",
		205 => "HTTP/1.1 205 Reset Content",
		206 => "HTTP/1.1 206 Partial Content",
		300 => "HTTP/1.1 300 Multiple Choices",
		301 => "HTTP/1.1 301 Moved Permanently",
		302 => "HTTP/1.1 302 Found",
		303 => "HTTP/1.1 303 See Other",
		304 => "HTTP/1.1 304 Not Modified",
		305 => "HTTP/1.1 305 Use Proxy",
		307 => "HTTP/1.1 307 Temporary Redirect",
		400 => "HTTP/1.1 400 Bad Request",
		401 => "HTTP/1.1 401 Unauthorized",
		402 => "HTTP/1.1 402 Payment Required",
		403 => "HTTP/1.1 403 Forbidden",
		404 => "HTTP/1.1 404 Not Found",
		405 => "HTTP/1.1 405 Method Not Allowed",
		406 => "HTTP/1.1 406 Not Acceptable",
		407 => "HTTP/1.1 407 Proxy Authentication Required",
		408 => "HTTP/1.1 408 Request Time-out",
		409 => "HTTP/1.1 409 Conflict",
		410 => "HTTP/1.1 410 Gone",
		411 => "HTTP/1.1 411 Length Required",
		412 => "HTTP/1.1 412 Precondition Failed",
		413 => "HTTP/1.1 413 Request Entity Too Large",
		414 => "HTTP/1.1 414 Request-URI Too Large",
		415 => "HTTP/1.1 415 Unsupported Media Type",
		416 => "HTTP/1.1 416 Requested range not satisfiable",
		417 => "HTTP/1.1 417 Expectation Failed",
		500 => "HTTP/1.1 500 Internal Server Error",
		501 => "HTTP/1.1 501 Not Implemented",
		502 => "HTTP/1.1 502 Bad Gateway",
		503 => "HTTP/1.1 503 Service Unavailable",
		504 => "HTTP/1.1 504 Gateway Time-out"
	);

	return @$http[$status];
}


/**
 * Returns the requested language of an http request
 *
 * @see		http://aktuell.de.selfhtml.org/artikel/php/httpsprache/
 *
 * @param	bool	$long_variant
 * @return	string
 */
function network_http_get_language($long_variant=false) {
	$lang_variable = @$_SERVER['HTTP_ACCEPT_LANGUAGE'];

	$accepted_languages = preg_split('/,\s*/', $lang_variable);
	foreach($accepted_languages as $accepted_language) {
		$res = preg_match ('/^([a-z]{1,8}(?:-[a-z]{1,8})*)'.
					'(?:;\s*q=(0(?:\.[0-9]{1,3})?|1(?:\.0{1,3})?))?$/i', $accepted_language, $matches);

		if(!$res) continue;
		$lang_code = explode('-', $matches[1]);

		$result = '';
		if(isset($lang_code[0])) $result = $lang_code[0];
		if($long_variant == true) {
			if(isset($lang_code[1])) {
				$result .= '_'.$lang_code[1];
			} else {
				$result .= '_'.$lang_code[0];
			}
		}

		return strtolower($result);
	}

	return '';
}


/**
 * Builds an array from URL parameters.
 *
 * @param	string	$parameter
 * @return	mixed
 */
function network_url_parameter_to_array($parameter) {
	$result = array();

	foreach(explode('&', $parameter) as $element) {
		$val = explode('=',  urldecode($element));
		$result[$val[0]] = $val[1];
	}

	return $result;
}


/**
 * PHP4 port of http_build_query
 *
 * @param	mixed	$data
 * @param	string	$prefix
 * @param	string	$sep
 * @param	string	$key
 * @return	string
 */
if (!function_exists('http_build_query')) {
	function http_build_query($data, $prefix='', $sep='', $key='') {
		$ret = array();
		foreach ((array)$data as $k => $v) {
			if (is_int($k) && $prefix != null) {
				$k = urlencode($prefix . $k);
			}

			if ((!empty($key)) || ($key === 0))  $k = $key.'['.urlencode($k).']';

			if (is_array($v) || is_object($v)) {
				array_push($ret, http_build_query($v, '', $sep, $k));
			} else {
				array_push($ret, $k.'='.urlencode($v));
			}
		}

		if (empty($sep)) $sep = ini_get('arg_separator.output');

		return implode($sep, $ret);
	}
}

?>