<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SlapperEntity;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\events\SlapperDeletionEvent;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SpawnSlapper
 * @package Xenophilicy\Smaccer\commands
 */
class RemoveSlapper extends SubSlapper {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapper.remove")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Slappers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only create Slappers in-game");
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
        if(!$entity instanceof SlapperEntity && !$entity instanceof SlapperHuman){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity is not handled by Slapper");
            return false;
        }
        $event = new SlapperDeletionEvent($entity);
        $event->call();
        $entity->close();
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Entity removed");
        return true;
    }
}