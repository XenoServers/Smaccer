<?php

namespace Xenophilicy\Smaccer\tasks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SpinEntityTask
 * @package Xenophilicy\Smaccer\tasks
 */
class SpinEntityTask extends Task {
    
    /**
     * Actions to execute when run
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick){
        foreach(Smaccer::getInstance()->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
                if($entity instanceof Player) continue;
                if(!$entity->namedtag->hasTag(SmaccerEntity::TAG_SPIN)) continue;
                if(in_array($entity->getSaveId(), ["SmaccerFallingSand", "SmaccerMinecart", "SmaccerBoat", "SmaccerPrimedTNT", "SmaccerShulker"])) continue;
                if(!$entity instanceof SmaccerEntity && !$entity instanceof SmaccerHuman) continue;
                $entity->setRotation($entity->getYaw() + ($entity->namedtag->getFloat(SmaccerEntity::TAG_SPIN) / 10), $entity->getPitch());
            }
        }
    }
}