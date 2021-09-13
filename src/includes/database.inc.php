<?php

function _init_database() {
	if(isset($GLOBALS['db'])) {
		return true;
	} else {
		// init adodb
		include_once($GLOBALS['config']['cms']['base_path'].'src/includes/libs/adodb/adodb-php/adodb.inc.php');

		// Set database cache directory
		if(!isset($ADODB_CACHE_DIR) && @$GLOBALS['config']['cms']['db_caching'] == true) {
			$ADODB_CACHE_DIR = $GLOBALS['config']['cms']['base_path'].'/cache/databases';
		}

		if(!defined('ADODB_ERROR_LOG_TYPE')) define('ADODB_ERROR_LOG_TYPE', 3);
		if(!defined('ADODB_ERROR_LOG_DEST')) define('ADODB_ERROR_LOG_DEST', $GLOBALS['config']['cms']['base_path'].'cache/logs/php-error.log');
		include_once($GLOBALS['config']['cms']['base_path'].'src/includes/libs/adodb/adodb-php/adodb-errorhandler.inc.php');

		$dsn = $GLOBALS['config']['database']['type'].'://'.$GLOBALS['config']['database']['user'].':'.$GLOBALS['config']['database']['password'].'@'.$GLOBALS['config']['database']['hostname'].(@$GLOBALS['config']['database']['port'] != '' ? ':'.$GLOBALS['config']['database']['port'] : '').'/'.$GLOBALS['config']['database']['database'];
		$conn = NewADOConnection($dsn);

		// Check if connection is open
		if(!$conn) {
			get_log()->error('No database connection.');
			die('Connection to database is not possible.');
		}

		// Set special settings after connection
		$ADODB_FETCH_MODE = isset($GLOBALS['config']['database']['fetch_mode']) ? $GLOBALS['config']['database']['fetch_mode'] : ADODB_FETCH_ASSOC;
		if(@$GLOBALS['config']['database']['encoding'] != '') {
			$conn->Execute('SET NAMES "'.$GLOBALS['config']['database']['encoding'].'"');
			$conn->Execute('SET CHARACTER SET '.$GLOBALS['config']['database']['encoding']);
		}
		$GLOBALS['db'] = $conn;
		return true;
	}
}



/**
 * Escapes a string to be used in a SQL query.
 *
 * @param	string	$str
 * @return	string
 */
function db_escape_string($str) {
	$value = "";

	switch($GLOBALS['config']['database']['type']) {

		case 'mysql':
			// Check if this function exists
			if(function_exists('mysql_real_escape_string')) {
				$value = mysql_real_escape_string($str);
			} else {
				$value = mysql_escape_string($str);
			}
			break;

		case 'mssql':
			if($str == chr(0)) $str = '';	// MSSQL 2000 has strange behavior with empty text fields
			$value = addslashes($str);
			break;

		default:
			$value = addslashes($str);
			break;
	}

	return $value;

}

/**
 * Verifies a value for database insertion.
 *
 * @param	string	$type
 * @param	string	$value
 * @return	string
 */
function db_prepare_value($type, $value) {
	switch($type) {
		case 'decimal':
			return str_replace(',', '.', $value);
			break;
	}

	return $value;
}


/**
 * Verifies a statement for a database query.
 *
 * @param	string	$sql
 * @param	string	$query_type
 * @param	mixed	$params
 * @return	string
 */
function db_prepare_statement($sql, $query_type='', $params=0) {
	$result = $sql;

	switch($GLOBALS['config']['database']['type']) {
		case 'mssql':
		case 'mssqli':

			// @see http://josephlindsay.com/archives/2005/05/27/paging-results-in-ms-sql-server/
			if($query_type == 'SELECT_LIMIT' && isset($params['primary_key'])) {
				$pos = strpos($sql, ' LIMIT ');
				if($pos !== false) {
					list($offset, $rows) = explode(',', substr($sql, $pos + 7));
					$result = substr($sql, 0, $pos);

					$order_by_pos = strpos($result, ' ORDER BY ');
					if($order_by_pos !== false) {
						$order_by = substr($result, $order_by_pos);
					} else {
						$order_by = '';
					}

					if($offset != 0) {
						$start_from = strpos($result, ' FROM ') + 6;
						$start_where = strpos($result, ' WHERE ');
						$start_select = strpos($result, 'SELECT ') + 7;

						$select_part = substr($result, $start_select, $start_from - 6 - $start_select);
						$from_part = substr($result, $start_from, $start_where - $start_from);

						$where_part = '';
						if($start_where !== false) {
							if($order_by_pos !== false) {
								$where_part = ' AND '.substr($result, $start_where + 6, $order_by_pos - $start_where - 6);
							} else {
								$where_part = ' AND '.substr($result, $start_where + 6);
							}
						}

						$inner_sql = 'SELECT TOP '.$offset.' '.$params['primary_key'].' FROM '.substr($result, $start_from);

						$result = 'SELECT TOP '.$rows.' '.$select_part.' FROM '.$from_part.' WHERE '.$params['primary_key'].' NOT IN ('.$inner_sql.') '.$where_part.' '.$order_by;
					} else {
						$result = preg_replace('/(^\s*select\s+(distinctrow|distinct)?)/i','\\1 top '.$rows.' ', $result);
					}
				}
			}
			break;

		case 'oracle':
			$result = preg_replace('|(?<!\\\)"|Usi', "'", $result);	// Replace Quotes

			// Replace all table synonyms with syntax "table_name as XYZ" to "table_name XYZ"
			preg_match('/SELECT(.*)\sWHERE\s(.*)/is', $result, $parts);
			if(!empty($parts)) {
				$result = 'SELECT '.str_replace(' as ', ' ', $parts[1]).' WHERE '.$parts[2];
			}
			break;

		case 'postgres':
			$result = preg_replace('|(?<!\\\)"|Usi', "'", $result);	// Replace Quotes
			break;
	}

	return $result;
}


/**
 * Wrapper functions for wrapper functions.
 */
if(!function_exists('db_insert_id')) {
	function db_insert_id() {
		return $GLOBALS['db']->Insert_Id();
	}
}

if(!function_exists('db_execute')) {
	function db_execute($sql) {
		$GLOBALS['db']->Execute($sql);
	}
}

if(!function_exists('db_autoexecute')) {
	/**
	 * AutoExecute wrapper with optional custom execution
	 *
	 * @param	string		$table
	 * @param	array		$arrFields
	 * @param	string		$mode
	 * @param	string|bool	$where
	 * @param	bool		$customAutoExecute
	 * @return	void
	 */
	function db_autoexecute($table, $arrFields, $mode, $where=false, $customAutoExecute=false) {
		if(isset($GLOBALS['config']['database']['custom_autoexecute_tables']) && is_array($GLOBALS['config']['database']['custom_autoexecute_tables'])) {
			if(in_array($table, $GLOBALS['config']['database']['custom_autoexecute_tables'])) {
				$customAutoExecute = true;
			}
		}

		if($customAutoExecute) {
			if($mode == 'INSERT') {
				$sql = 'INSERT INTO `'.$table.'` (';
				$keys = array_keys($arrFields);
				foreach($keys as $key) {
					$sql .= '`'.$key.'`,';
				}
				if(substr($sql, - 1) == ',') {
					$sql = substr($sql, 0, strlen($sql) - 1);
				}

				$sql .= ') VALUES (';

				foreach($arrFields as $value) {
					switch(gettype($value)) {
						default:
							$sql .= '"'.db_escape_string($value).'",';
							break;
						case 'integer':
							$sql .= (int)$value.',';
							break;
					}
				}

				if(substr($sql, - 1) == ',') {
					$sql = substr($sql, 0, strlen($sql) - 1);
				}
				$sql .= ')';

				db_execute($sql);
			} else {
				if($where !== false) {
					$sql = 'UPDATE `'.$table.'` SET ';
					foreach($arrFields as $key => $value) {
						$sql .= '`'.$key.'` = ';
						switch(gettype($value)) {
							default:
								$sql .= '"'.db_escape_string($value).'",';
								break;
							case 'integer':
								$sql .= (int)$value.',';
								break;
						}
					}
					if(substr($sql, - 1) == ',') {
						$sql = substr($sql, 0, strlen($sql) - 1);
					}
					$sql .= ' WHERE '.$where;

					db_execute($sql);
				}
			}
		} else {
			$GLOBALS['db']->AutoExecute($table, $arrFields, $mode, $where);
		}
	}
}

if(!function_exists('db_get_one')) {
	function db_get_one($sql, $cached=false) {
		if($cached && $GLOBALS['env']['db_caching']) {
			$result = $GLOBALS['db']->CacheGetOne($GLOBALS['config']['database']['cache_time'], $sql);
		} else {
			$result = $GLOBALS['db']->GetOne($sql);
		}
		return $result;
	}
}

if(!function_exists('db_get_col')) {
	function db_get_col($sql, $cached=false) {
		if($cached && $GLOBALS['env']['db_caching']) {
			$result = $GLOBALS['db']->CacheGetCol($GLOBALS['config']['database']['cache_time'], $sql);
		} else {
			$result = $GLOBALS['db']->GetCol($sql);
		}
		return $result;
	}
}

if(!function_exists('db_get_row')) {
	function db_get_row($sql, $cached=false) {
		if($cached && $GLOBALS['env']['db_caching']) {
			$result = $GLOBALS['db']->CacheGetRow($GLOBALS['config']['database']['cache_time'], $sql);
		} else {
			$result = $GLOBALS['db']->GetRow($sql);
		}

		if($GLOBALS['config']['database']['type'] == 'oracle') {
			foreach($result as $key => $value) {
				unset($result[$key]);
				$result[strtolower($key)] = $value;
			}
		}

		return $result;
	}
}

if(!function_exists('db_get_assoc')) {
	function db_get_assoc($sql, $cached=false) {
		if($cached && $GLOBALS['env']['db_caching']) {
			$result = $GLOBALS['db']->CacheGetAssoc($GLOBALS['config']['database']['cache_time'], $sql);
		} else {
			$result = $GLOBALS['db']->GetAssoc($sql);
		}
		return $result;
	}
}

if(!function_exists('db_get_all')) {
	function db_get_all($sql, $cached=false) {
		if($cached && $GLOBALS['env']['db_caching']) {
			$result = $GLOBALS['db']->CacheGetAll($GLOBALS['config']['database']['cache_time'], $sql);
		} else {
			$result = $GLOBALS['db']->GetAll($sql);
		}

		if($GLOBALS['config']['database']['type'] == 'oracle') {
			foreach($result as $key => $item) {
				foreach($item as $k => $v) {
					unset($item[$k]);
					$item[strtolower($k)] = $v;
				}
				$result[$key] = $item;
			}
		}

		return $result;
	}
}
?>