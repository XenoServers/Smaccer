<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class BaseSlapper
 * @package Xenophilicy\BaseSlapper\commands
 */
class RCA extends PluginCommand {
    
    public function __construct(){
        parent::__construct("slapper", Smaccer::getInstance());
        $this->setPermission("slapper.rca");
        $this->setDescription("Run commands as other players");
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) < 2){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /rca <player> <command>");
            return false;
        }
        $name = array_shift($args);
        $player = Smaccer::getInstance()->getServer()->getPlayer($name);
        if(!$player instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That player doesn't exist");
            return false;
        }
        Smaccer::getInstance()->getServer()->dispatchCommand($player, trim(implode(" ", $args)));
        return true;
    }
}