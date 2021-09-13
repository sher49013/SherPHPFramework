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
