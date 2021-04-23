<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class CancelSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class CancelSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return bool
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only cancel hit and ID sessions in-game");
            return false;
        }
        if(!isset(Smaccer::getInstance()->hitSessions[$sender->getName()]) && !isset(Smaccer::getInstance()->idSessions[$sender->getName()])){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have a session to cancel");
            return false;
        }
        unset(Smaccer::getInstance()->hitSessions[$sender->getName()]);
        unset(Smaccer::getInstance()->idSessions[$sender->getName()]);
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Cancelled session");
        return true;
    }
}