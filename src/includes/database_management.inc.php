<?php
/**
 * Functions for database management.
 *
 * @author	HAMAD ALI (ali sher)
 *
 */


/**
 * Flush database caches.
 *
 * @return	void
 */
function db_flush_cache() {
	$GLOBALS['db']->CacheFlush();
}


/**
 * Truncate a table.
 *
 * @param	table
 * @return	void
 */
function db_truncate_table($table){

	switch($GLOBALS['config']['database']['type']) {

		case 'firebird':
			$sql = 'DELETE FROM '.$table;
			break;

		default:
			$sql = 'TRUNCATE TABLE '.$table;
			break;

	}

	db_execute($sql);
}


/**
 * Does table exist ?
 *
 * @param	table
 * @return	bool
 */
function db_table_exists($table) {
	$tables = db_get_col('SHOW TABLES');
	return in_array($table, $tables);
}


/**
 * Function that returns the current version
 * of the database server.
 *
 * @return	string
 */
function db_get_version() {
	switch(strtolower($GLOBALS['config']['database']['type'])) {
		case 'mysql':
			$result = db_get_row('SELECT VERSION() version');	// Do it like this, because MySQL 3.x does not support a "LIMIT" in this query when using GetOne-Method
			return isset($result['version']) ? $result['version'] : 'unknown';
			break;

		case 'mssql':
			return db_get_one('SELECT @@Version');
			break;
			
		default:
			return '';
			break;
	}
}


/**
 * Returns a table's schema.
 *
 * @param	string	$table
 * @return	string
 */
function db_get_table_schema($table) {
	$default_no_escape = array('CURRENT_TIMESTAMP', 'CURRENT_TIME', 'CURRENT_DATE', 'FROM_UNIXTIME');

	$sql = 'DROP TABLE IF EXISTS '.$table.';'."\n";

	$sql .= 'CREATE TABLE '.$table.' ('."\n";

	$fields = $GLOBALS['db']->GetAll('SHOW FIELDS FROM '.$table);
	foreach($fields as $field) {
		$sql .= '  '.$field['Field'].' '.$field['Type'];

		if(strlen($field['Default']) > 0) {
			$sql .= ' default ';
			if(!in_array($field['Default'], $default_no_escape)) $sql .= '\'';
			$sql .= $field['Default'];
			if(!in_array($field['Default'], $default_no_escape)) $sql .= '\'';
		}

		if($field['Null'] != 'YES') $sql .= ' not null';
		if(isset($fields['Extra'])) $sql .= ' '.$field['Extra'];
		$sql .= ','."\n";
	}


	// Add keys
	$index = array();
	$keys = $GLOBALS['db']->GetAll('SHOW KEYS FROM '.$table);
	foreach($keys as $key) {
		$kname = $key['Key_name'];
		if(!isset($index[$kname])) {
			$index[$kname] = array('unique' => !$key['Non_unique'], 'columns' => array());
		}
		$index[$kname]['columns'][] = $key['Column_name'];
	}
	$sql = preg_replace(",\n$", '', $sql);

	while(list($kname, $info) = each($index)) {
		$sql .= ','."\n";
		$columns = implode($info['columns'], ', ');
		if ($kname == 'PRIMARY') {
			$sql .= '  PRIMARY KEY (' . $columns . ')';
		} elseif ($info['unique']) {
			$sql .= '  UNIQUE '.$kname.' ('.$columns.')';
		} else {
			$sql .= '  KEY '.$kname.' ('.$columns.')';
		}
	}
	$sql .= "\n".')';


	// Get engine and comment
	$status = db_get_row('SHOW TABLE STATUS LIKE "'.$table.'"');
	if(@$status['Engine'] != '') $sql .= ' ENGINE='.$status['Engine'];
	if(@$status['Engine'] != '') $sql .= ' COMMENT=\''.$status['Comment'].'\'';

	$sql .= ';'."\n";

	return $sql;
}


/**
 * Returns table's content as INSERT statements.
 *
 * @param	string	$table
 * @return	string
 */
function db_get_table_dump($table) {
	$sql = '';

	$records = $GLOBALS['db']->GetAll('SELECT * FROM '.$table);

	foreach($records as $record) {
		$line = 'INSERT INTO '.$table.' VALUES (';

		foreach($record as $key => $field) {
			if(is_numeric($key)) {
				$line .= '\''.db_escape_string($field).'\', ';
			}
		}

		$line = substr($line , 0, strlen($line) - 2).");\n";

		$sql .= $line;
	}

	return $sql;
}


/**
 * Adds a Column to a Datatable
 *
 * @param	string	$table_name
 * @param	string	$column_name
 * @param	string	$type
 * @param	string	$length
 * @param	string	$default
 * @param	string	$index
 */
function db_add_column($table_name, $column_name, $type, $length, $default, $index) {
	db_execute(db_prepare_statement('ALTER TABLE `' . $table_name . '` ADD `' . $column_name . '` ' . $type . (empty($length) ? '' : ' ( ' . $length . ' ) ') . ' NOT NULL ' . $default . ($index != '' ? ', ADD ' . $index . '( `' . $column_name . '` )' : '') ));
}


/**
 * Modifies a Column of a Datatable
 *
 * @param	string	$table_name
 * @param	string	$old_column_name
 * @param	string	$new_column_name
 * @param	string	$type
 * @param	string	$length
 * @param	string	$default
 */
function db_modify_column($table_name, $old_column_name, $new_column_name, $type, $length, $default) {
	db_execute(db_prepare_statement('ALTER TABLE `' . $table_name . '` CHANGE `' . $old_column_name . '` `' . $new_column_name . '` ' . $type . (empty($length) ? '' : ' ( ' . $length . ' ) ') . ' NOT NULL ' . $default));
}


/**
 * Deletes a Column of a Datatable
 *
 * @param	string	$table_name
 * @param	string	$column_name
 */
function db_drop_column($table_name, $column_name) {
	db_execute(db_prepare_statement('ALTER TABLE `' . $table_name . '` DROP `' . $column_name . '`'));
}


/**
 * Checks whether a Column exists in given Datatable
 *
 * @param	string	$table_name
 * @param	string	$column_name
 * @return	bool
 */
function db_column_exists($table_name, $column_name) {
	$fields = db_get_all(db_prepare_statement('SHOW FIELDS FROM ' . $table_name));
	$found = false;
	foreach ($fields as $field) { 
		if ($field['Field'] == $column_name) {
			$found = true;
			break;
		}
	}
	return $found;
}

?>