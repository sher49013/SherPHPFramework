<?php
// Error Handling
ini_set('log_errors', 1);
ini_set('error_log', dirname(__FILE__).'/../cache/logs/php-error.log');
ini_set('display_errors', true);

// Force unicode and UTC
mb_internal_encoding('UTF-8');
mb_http_output('UTF-8');
mb_http_input('UTF-8');
mb_language('uni');
mb_regex_encoding('UTF-8');
date_default_timezone_set('UTC');

$GLOBALS['env']['timezone'] = 'UTC';

$GLOBALS['config']['cms'] = array();

define('BASE_PATH', realpath(dirname(__FILE__) . '/../') . '/');
$GLOBALS['config']['cms']['base_path'] = BASE_PATH;
$GLOBALS['config']['cms']['cache_folder'] = $GLOBALS['config']['cms']['base_path'].'cache/';
$GLOBALS['config']['cms']['fs_data_path'] = $GLOBALS['config']['cms']['base_path'].'cache/data/';
$GLOBALS['config']['cms']['logs'] = $GLOBALS['config']['cms']['cache_folder'].'logs/';
$GLOBALS['config']['cms']['design_path'] = 'fs/design/sher/';
$GLOBALS['config']['cms']['theme_path'] = $GLOBALS['config']['cms']['design_path'].'theme/';
$GLOBALS['config']['cms']['data_cache_limit'] = 5 * 60; // 5 mins
$GLOBALS['config']['cms']['2fa_enabled'] = false;
$GLOBALS['config']['cms']['allowedIps'] = array('127.0.0.1');
$GLOBALS['config']['cms']['access_token'] = '';

/** Database Setting **/
$GLOBALS['config']['database']['table_prefix'] = '';
$GLOBALS['config']['database']['type']         = 'mysqli';
$GLOBALS['config']['database']['user']         = 'root';
$GLOBALS['config']['database']['password']     = 'DB_PASSWORD';
$GLOBALS['config']['database']['hostname']     = 'localhost';
$GLOBALS['config']['database']['port']         = '';
$GLOBALS['config']['database']['database']     = 'DB_NAME';
$GLOBALS['config']['database']['encoding']     = 'utf8';

/** System Setting **/
$GLOBALS['config']['application']['session_name'] = 'SID';
$GLOBALS['config']['cms']['title'] = '';
$GLOBALS['config']['cms']['site_url'] = '';
$GLOBALS['config']['cms']['copyright'] = 'Sher';
$GLOBALS['config']['cms']['build_version'] = '0.1';
$GLOBALS['config']['cms']['page_limit'] = 25;
$GLOBALS['config']['cms']['avail_lang'] = array('en_us', 'de_de');

$GLOBALS['config']['cms']['db_caching'] = false;

$GLOBALS['env']['is_ssl'] = true;

// Session lifetime Settings
$GLOBALS['config']['session.gc_maxlifetime'] = 24 * 60 * 60; // 24 hr

/** Email Setting **/
$GLOBALS['config']['mail']['smtp_port'] = '';
$GLOBALS['config']['mail']['smtp_host'] = '';
$GLOBALS['config']['mail']['smtp_secure'] = '';
$GLOBALS['config']['mail']['smtp_username'] = '';
$GLOBALS['config']['mail']['smtp_password'] = '';

/** languages **/
$GLOBALS['config']['cms']['avail_lang'] = array('en_us', 'de_de');

//Security Key For Production
$GLOBALS['config']['system_key'] = '%security_key%';
$GLOBALS['env']['is_windows'] = false;

/**************************************************************************
 * include local conf to overwrite settings
 *************************************************************************/
if(file_exists(__DIR__.'/config.local.php')) {
    include_once(__DIR__.'/config.local.php');
}

include_once($GLOBALS['config']['cms']['base_path'].'src/utils.inc.php');
