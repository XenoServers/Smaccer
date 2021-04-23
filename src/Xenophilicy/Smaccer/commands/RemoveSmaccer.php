<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\events\SmaccerDeletionEvent;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SpawnSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class RemoveSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.remove")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Smaccers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only create Smaccers in-game");
            return false;
        }
        if(!isset($args[0])){
            Smaccer::getInstance()->hitSessions[$sender->getName()] = true;
            $sender->sendMessage(Smaccer::PREFIX . TF::YELLOW . "Hit an entity to remove it");
            return true;
        }
        $entity = $sender->getLevel()->getEntity((int)$args[0]);
        if($entity === null){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Entity does not exist");
            return false;
        }
        if(!$entity instanceof SmaccerEntity && !$entity instanceof SmaccerHuman){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity is not handled by Smaccer");
            return false;
        }
        $event = new SmaccerDeletionEvent($entity);
        $event->call();
        $entity->flagForDespawn();
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Entity removed");
        return true;
    }
}