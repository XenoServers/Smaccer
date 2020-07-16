<?php

namespace Xenophilicy\Smaccer\tasks;

use pocketmine\scheduler\Task;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class QueryTaskCaller
 * @package Xenophilicy\Syncount\Task
 */
class QueryTaskCaller extends Task {
    
    private $host;
    private $port;
    
    /**
     * QueryTaskCaller constructor.
     * @param string $host
     * @param int $port
     */
    public function __construct(string $host, int $port){
        $this->host = $host;
        $this->port = $port;
    }
    
    /**
     * Actions to execute when run
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick){
        Smaccer::getInstance()->getServer()->getAsyncPool()->submitTask(new QueryTask($this->host, $this->port));
    }
}