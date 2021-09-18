<?php
/**
 * Migration File
 *
 * @package     
 * @subpackage	
 *
 * @author HAMAD ALI (ali sher)
 * @copyright	
 *
 * @version	$Id$
 */

/**
 * !!!
 * Please use migration files from sub folder migrations now. Do not change anything in this file anymore.
 * !!!
 */
$GLOBALS['config']['no_session'] = true;
include_once __DIR__.'/../configs/config.inc.php';
ini_set('display_errors', true);
if (!db_table_exists('core_sessions')) {
    // Need to remove the NO_ZERO_IN_DATE,NO_ZERO_DATE modes, as the're breaking the DEFAULT '0000-00-00 00:00:00'
//    db_execute("SET sql_mode = 'NO_AUTO_CREATE_USER,NO_ENGINE_SUBSTITUTION';");

    db_execute("
                CREATE TABLE `core_sessions` (
              `sesskey` varchar(64) NOT NULL DEFAULT '',
              `expiry` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
              `expireref` varchar(250) DEFAULT '',
              `created` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `modified` timestamp NOT NULL DEFAULT '0000-00-00 00:00:00',
              `sessdata` longtext,
              `user_name` varchar(255) DEFAULT NULL,
              `user_agent` varchar(255) DEFAULT NULL,
              `user_ip` varchar(255) DEFAULT NULL,
              `script_name` varchar(255) DEFAULT NULL
            ) ENGINE=MyISAM DEFAULT CHARSET=utf8 COMMENT='Holds all user sessions. Structure has been given by ADOdb.';
                ");

    db_execute("
                ALTER TABLE `core_sessions`
              ADD PRIMARY KEY (`sesskey`),
              ADD KEY `sess2_expiry` (`expiry`),
              ADD KEY `sess2_expireref` (`expireref`),
              ADD KEY `user_name` (`user_name`),
              ADD KEY `user_agent` (`user_agent`),
              ADD KEY `user_ip` (`user_ip`);
                ");
}

/**
 * Read migration path and execute migrations
 */
$migrationPath = __DIR__.'/migrations/';
$migrations = scandir($migrationPath, SCANDIR_SORT_ASCENDING);
foreach($migrations as $migrate) {
    if (strpos($migrate, 'migrate') !== false && pathinfo($migrate, PATHINFO_EXTENSION) === 'php') {
        $migrationFile = $migrationPath . $migrate;
        if (!file_exists($migrationFile)) {
            die('CRITICAL: File ' . $migrationFile . ' not found!');
        }

        // Load the migrations file
        include $migrationFile;

        // Get class name and run migration from it
        $className = basename(substr($migrate, strpos($migrate, 'migrate_')), '.php');
        if (method_exists($className, 'migrate')) {
            $className::migrate();
        }
    }
}
