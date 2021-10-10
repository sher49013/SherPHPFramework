# SherPHPFramework

## Configuraiton
With help of config file you manage the configuration for both envirment local and prod. (configs/config.inc.php, configs/config.local.php)
configuration like site url, database, cahe path log path, system setting, email setting, security setting, languages setting, session setting etc

## Database
you need to add data setting in configs/config.inc.php file
$GLOBALS['config']['database']['type']         = 'mysqli';
$GLOBALS['config']['database']['user']         = 'root';
$GLOBALS['config']['database']['password']     = 'password';
$GLOBALS['config']['database']['hostname']     = 'localhost';
$GLOBALS['config']['database']['port']         = '3306';
$GLOBALS['config']['database']['database']     = 'SherPHPFrameWork';
$GLOBALS['config']['database']['encoding']     = 'utf8';

## Migration
with help of deploy you manage the migrations file and database. you add migration file deploy/migrations folder file file 20200619_migrate_SHER_1.php.
Migration file name <yyyymmdd>_migrate<project name>_<number>.php. You run migration with php deploy/migrate.php command.

## Log
you can add log with $GLOBALS['log']->info('run');. This log is save in "cache/logs/app.log" file

## Cron
you can add cron with cron directory. you add file in directory and set some param llike cron name and time. add cron name in cron.php $crons array. set cron.php in your server cron tab.
