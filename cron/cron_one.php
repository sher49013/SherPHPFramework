<?php
class cron_one
{
    /**
     * cron name
     * @var string
     */
    public $name = "one";

    /**
     * cron time
     * @var int
     */
    public $time = 60;


    public function __construct()
    {
    
    }

    public function run()
    {
        /*
         * please check cache/logs/app.log file to verify cron
         */
        $GLOBALS['log']->info('run');
        
    }
}