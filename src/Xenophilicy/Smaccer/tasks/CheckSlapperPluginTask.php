<?php

namespace Xenophilicy\Smaccer\tasks;

use pocketmine\scheduler\Task;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class CheckSlapperPluginTask
 * @package Xenophilicy\Smaccer\tasks
 */
class CheckSlapperPluginTask extends Task {
    
    /**
     * Actions to execute when run
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick){
        if(!is_null(Smaccer::getInstance()->getServer()->getPluginManager()->getPlugin("Slapper"))){
            Smaccer::getInstance()->getLogger()->critical("It seems that you're still using Slapper while trying to load Smaccer. Please remove Slapper before trying to use this plugin.");
            Smaccer::getInstance()->getServer()->getPluginManager()->disablePlugin(Smaccer::getInstance());
        }
    }
}