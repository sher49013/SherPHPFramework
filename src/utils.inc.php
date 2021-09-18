<?php

include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/libs/autoload.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/database.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/session.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/io.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/network.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/logging_engine.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/date_time.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/database_management.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/mail_management.inc.php');
include_once($GLOBALS['config']['cms']['base_path'] . 'src/includes/mail.inc.php');

init_session();

/**
 * This function is use for get element in array
 * @param $array
 * @param $key
 * @param string $default
 * @return mixed|string
 */
function arrayGet($array, $key, $default = '')
{
    return isset($array[$key]) ? db_escape_string($array[$key]) : db_escape_string($default);
}

/**
 * This funciton is use for generate password
 * @param $password
 * @return string
 */
function securityHashPassword($password)
{
    return md5($GLOBALS['config']['system_key'] . $password);
}