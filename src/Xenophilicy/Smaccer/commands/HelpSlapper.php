<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class HelpSlapper
 * @package Xenophilicy\Smaccer\commands
 */
class HelpSlapper extends SubSlapper {
    
    private const STAR = TF::GREEN . " * " . TF::LIGHT_PURPLE;
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapper.help")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to view Smaccer help");
            return false;
        }
        if(isset($args[0]) && array_shift($args) === "edit"){
            $sender->sendMessage(Smaccer::PREFIX . TF::AQUA . "---- " . TF::GOLD . "Slapper Edit Help " . TF::AQUA . " ----");
            $sender->sendMessage(self::STAR . "armor: /slapper edit <eid> armor");
            $sender->sendMessage(self::STAR . "helmet: /slapper edit <eid> helmet [id[:meta]]");
            $sender->sendMessage(self::STAR . "chestplate: /slapper edit <eid> chestplate [id[:meta]]");
            $sender->sendMessage(self::STAR . "leggings: /slapper edit <eid> leggings [id[:meta]]");
            $sender->sendMessage(self::STAR . "boots: /slapper edit <eid> boots [id[:meta]]");
            $sender->sendMessage(self::STAR . "hand: /slapper edit <eid> boots [id[:meta]]");
            $sender->sendMessage(self::STAR . "skin: /slapper edit <eid> skin");
            $sender->sendMessage(self::STAR . "name: /slapper edit <eid> name <name|remove>");
            $sender->sendMessage(self::STAR . "addcommand: /slapper edit <eid> addcommand <command>");
            $sender->sendMessage(self::STAR . "delcommand: /slapper edit <eid> delcommand <command>");
            $sender->sendMessage(self::STAR . "listcommands: /slapper edit <eid> listcommands");
            $sender->sendMessage(self::STAR . "blockid: /slapper edit <eid> block <id[:meta]>");
            $sender->sendMessage(self::STAR . "scale: /slapper edit <eid> scale <size>");
            $sender->sendMessage(self::STAR . "tphere: /slapper edit <eid> tphere");
            $sender->sendMessage(self::STAR . "tpto: /slapper edit <eid> tpto");
            $sender->sendMessage(self::STAR . "menuname: /slapper edit <eid> menuname <name|remove>");
        }else{
            $sender->sendMessage(Smaccer::PREFIX . TF::AQUA . "---- " . TF::GOLD . "Slapper Help " . TF::AQUA . " ----");
            $sender->sendMessage(self::STAR . "spawn: /slapper spawn <type> [name]");
            $sender->sendMessage(self::STAR . "edit: /slapper edit <mode>");
            $sender->sendMessage(self::STAR . "id: /slapper id");
            $sender->sendMessage(self::STAR . "remove: /slapper remove");
            $sender->sendMessage(self::STAR . "cancel: /slapper cancel");
            $sender->sendMessage(self::STAR . "list: /slapper list [level]");
            $sender->sendMessage(self::STAR . "help: /slapper help [edit]");
        }
        return true;
    }
}