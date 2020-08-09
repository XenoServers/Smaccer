<?php

namespace Xenophilicy\Smaccer\tasks;

use pocketmine\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\QueryManager;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class UpdateCountTagTask
 * @package Xenophilicy\Smaccer\tasks
 */
class UpdateCountTagTask extends Task {
    
    /**
     * Actions to execute when run
     * @param int $currentTick
     * @return void
     */
    public function onRun(int $currentTick){
        foreach(Smaccer::getInstance()->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
                if($entity instanceof Player) continue;
                if($entity->namedtag->hasTag(SmaccerEntity::TAG_SERVER)){
                    $online = 0;
                    $maximum = 0;
                    $servers = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER);
                    foreach($servers as $server){
                        $data = QueryManager::getResult($server->getValue());
                        if(is_null($data)) continue;
                        $online += $data[0];
                        $maximum += $data[1];
                    }
                    if($maximum === 0) $online = $maximum = "-";
                    $format = Smaccer::$settings["Default"]["count-tags"]["servers"];
                    $nametag = str_replace(["{players}", "{maximum}"], [$online, $maximum], $format);
                    $entity->setNameTag($entity->namedtag->getString(SmaccerEntity::TAG_NAME) . TF::EOL . $nametag);
                    continue;
                }
                if($entity->namedtag->hasTag(SmaccerEntity::TAG_WORLD)){
                    $online = 0;
                    $worlds = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_WORLD);
                    foreach($worlds as $world){
                        $level = Smaccer::getInstance()->getServer()->getLevelByName($world->getValue());
                        if(is_null($level)) continue;
                        $online += sizeof($level->getPlayers());
                    }
                    $format = Smaccer::$settings["Default"]["count-tags"]["worlds"];
                    $nametag = str_replace("{players}", $online, $format);
                    $entity->setNameTag($entity->namedtag->getString(SmaccerEntity::TAG_NAME) . TF::EOL . $nametag);
                    continue;
                }
                $entity->setNameTag($entity->namedtag->getString(SmaccerEntity::TAG_NAME, ""));
            }
        }
    }
}