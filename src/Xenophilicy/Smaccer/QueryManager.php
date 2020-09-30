<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use Xenophilicy\Smaccer\tasks\QueryTaskCaller;

/**
 * Class QueryManager
 * @package Xenophilicy\Smaccer
 */
class QueryManager {
    
    private static $results = [];
    
    /**
     * @param string $server
     */
    public static function initServer(string $server){
        if(in_array($server, self::$results)) return;
        $data = explode(":", $server);
        $ip = $data[0];
        $port = (int)$data[1];
        $task = Smaccer::getInstance()->getScheduler()->scheduleRepeatingTask(new QueryTaskCaller($ip, $port), Smaccer::$settings["Default"]["query-interval"]);
        self::$results[$server]["taskid"] = $task->getTaskId();
    }
    
    /**
     * @return array
     */
    public static function getAllServers(){
        return self::$results;
    }
    
    /**
     * @param string $server
     * @param array $data
     */
    public static function depositResult(string $server, array $data){
        self::$results[$server]["query"] = $data;
    }
    
    /**
     * @param string $server
     * @return mixed|null
     */
    public static function getResult(string $server){
        return self::$results[$server]["query"] ?? null;
    }
}