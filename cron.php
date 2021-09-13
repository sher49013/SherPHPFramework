<?php
$GLOBALS['config']['no_session'] = true;

include_once 'configs/config.inc.php';

$flush_db = false;

$crons = array(
	'one'
);

$cronPath = __DIR__.'/cron/';
foreach($crons as $cron) {
    $cronFile = $cronPath . 'cron_' . $cron . '.php';
    if (!file_exists($cronFile)) {
        die('CRITICAL: File ' . $cronFile . ' not found!');
    }

    include_once $cronFile;

    $className = 'cron_' . $cron;
    if (class_exists($className)) {
        $classObject = new $className();
        if (method_exists($classObject, 'run')) {
            $classObject->run();
        }
    }
}

