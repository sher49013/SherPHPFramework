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
        if (property_exists($classObject, 'name') && property_exists($classObject, 'time') && method_exists($classObject, 'run')) {
            if (cronTime($classObject->name, $classObject->time)) {
                $classObject->run();
                setNextTime($classObject->name);
            }
        }
    }
}

/**
 * @param $name
 * @param $time
 * @return bool
 */
function cronTime($name, $time)
{
    $cron_cache[$name] = time();
    $cron_cache_file = $GLOBALS['config']['cms']['cache_folder'].'data/cronDataSync.json';
    if(io_file_exists($cron_cache_file)) {
        $cron_cache = json_decode(io_file_get_contents($cron_cache_file), true);
        $last_executed = @$cron_cache[$name];
        $cron_cache[$name] = time();
        if ($last_executed + (int)$time < time() ) {
            return true;
        }
        return false;
    }
    return true;
}

/**
 * @param $name
 */
function setNextTime($name)
{
    $cron_cache_file = $GLOBALS['config']['cms']['cache_folder'].'data/cronDataSync.json';
    $cron_cache[$name] = time();
    if(io_file_exists($cron_cache_file)) {
        $cron_cache = json_decode(io_file_get_contents($cron_cache_file), true);
        $cron_cache[$name] = time();
    }
    io_file_put_contents($cron_cache_file, json_encode($cron_cache));
}

