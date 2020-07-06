<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class IdSlapper
 * @package Xenophilicy\Smaccer\commands
 */
class IdSlapper extends SubSlapper {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapper.id")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Slappers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only grab Slapper IDs in-game");
            return false;
        }
        Smaccer::getInstance()->idSessions[$sender->getName()] = true;
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Hit a Slapper to get its ID");
        return true;
    }
}