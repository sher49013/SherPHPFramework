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