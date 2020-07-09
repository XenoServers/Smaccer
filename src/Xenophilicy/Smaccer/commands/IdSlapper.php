<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class IdSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class IdSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.id")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Smaccers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only grab Smaccer IDs in-game");
            return false;
        }
        Smaccer::getInstance()->idSessions[$sender->getName()] = true;
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Hit a Smaccer to get its ID");
        return true;
    }
}