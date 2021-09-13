<?php
function _init_session_management() {
	global $ADODB_SESSION_DRIVER;
	global $ADODB_SESSION_CONNECT;
	global $ADODB_SESSION_USER;
	global $ADODB_SESSION_PWD;
	global $ADODB_SESSION_DB;
	global $ADODB_SESS_CONN;
	global $ADODB_SESSION_TBL;
	global $ADODB_SESS_LIFE;

	$ADODB_SESSION_DRIVER = $GLOBALS['config']['database']['type'];
	$ADODB_SESSION_CONNECT = $GLOBALS['config']['database']['hostname'];
	$ADODB_SESSION_USER = $GLOBALS['config']['database']['user'];
	$ADODB_SESSION_PWD = $GLOBALS['config']['database']['password'];
	$ADODB_SESSION_DB = $GLOBALS['config']['database']['database'];

	if(!isset($ADODB_SESSION_TBL)) $ADODB_SESSION_TBL = 'core_sessions';
	if(isset($GLOBALS['config']['session.gc_maxlifetime'])) $ADODB_SESS_LIFE = $GLOBALS['config']['session.gc_maxlifetime'];
	session_name($GLOBALS['config']['application']['session_name']);

	include_once($GLOBALS['config']['cms']['base_path'].'src/includes/libs/adodb/adodb-php/session/adodb-session2.php');

	$ADODB_SESS_CONN = $GLOBALS['db'];
}

function init_session() {
	_init_database();
	
	if(empty($GLOBALS['config']['no_session'])) {
		_init_session_management();
		session_start();

		if(!isset($_SESSION['user_data']['logged_in'])) {
			$_SESSION['user_data']['logged_in'] = false;
		}
	}
}
?>