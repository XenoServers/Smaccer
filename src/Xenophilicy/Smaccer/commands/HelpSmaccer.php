<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class HelpSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class HelpSmaccer extends SubSmaccer {
    
    private const STAR = TF::GREEN . " * " . TF::LIGHT_PURPLE;
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.help")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to view Smaccer help");
            return false;
        }
        if(isset($args[0]) && array_shift($args) === "edit"){
            $sender->sendMessage(Smaccer::PREFIX . TF::AQUA . "---- " . TF::GOLD . "Smaccer Edit Help " . TF::AQUA . " ----");
            $sender->sendMessage(self::STAR . "armor: /smaccer edit <eid> armor");
            $sender->sendMessage(self::STAR . "helmet: /smaccer edit <eid> helmet [id[:meta]]");
            $sender->sendMessage(self::STAR . "chestplate: /smaccer edit <eid> chestplate [id[:meta]]");
            $sender->sendMessage(self::STAR . "leggings: /smaccer edit <eid> leggings [id[:meta]]");
            $sender->sendMessage(self::STAR . "boots: /smaccer edit <eid> boots [id[:meta]]");
            $sender->sendMessage(self::STAR . "hand: /smaccer edit <eid> boots [id[:meta]]");
            $sender->sendMessage(self::STAR . "skin: /smaccer edit <eid> skin");
            $sender->sendMessage(self::STAR . "name: /smaccer edit <eid> name <name|remove>");
            $sender->sendMessage(self::STAR . "addcommand: /smaccer edit <eid> addcommand <command>");
            $sender->sendMessage(self::STAR . "delcommand: /smaccer edit <eid> delcommand <command>");
            $sender->sendMessage(self::STAR . "listcommands: /smaccer edit <eid> listcommands");
            $sender->sendMessage(self::STAR . "blockid: /smaccer edit <eid> block <id[:meta]>");
            $sender->sendMessage(self::STAR . "scale: /smaccer edit <eid> scale <size>");
            $sender->sendMessage(self::STAR . "tphere: /smaccer edit <eid> tphere");
            $sender->sendMessage(self::STAR . "tpto: /smaccer edit <eid> tpto");
            $sender->sendMessage(self::STAR . "menuname: /smaccer edit <eid> menuname <name|remove>");
        }else{
            $sender->sendMessage(Smaccer::PREFIX . TF::AQUA . "---- " . TF::GOLD . "Smaccer Help " . TF::AQUA . " ----");
            $sender->sendMessage(self::STAR . "spawn: /smaccer spawn <type> [name]");
            $sender->sendMessage(self::STAR . "edit: /smaccer edit <mode>");
            $sender->sendMessage(self::STAR . "id: /smaccer id");
            $sender->sendMessage(self::STAR . "remove: /smaccer remove");
            $sender->sendMessage(self::STAR . "cancel: /smaccer cancel");
            $sender->sendMessage(self::STAR . "list: /smaccer list [level]");
            $sender->sendMessage(self::STAR . "help: /smaccer help [edit]");
        }
        return true;
    }
}