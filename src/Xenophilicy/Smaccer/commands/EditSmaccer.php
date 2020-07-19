<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\item\Item;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\convert\RuntimeBlockMapping;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\other\SmaccerFallingSand;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\QueryManager;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class EditSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class EditSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.edit")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to edit Smaccers");
            return false;
        }
        $eid = array_shift($args);
        if(is_null($eid)){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> <mode> [args]");
            return false;
        }
        $entity = null;
        foreach(Smaccer::getInstance()->getServer()->getLevels() as $level){
            $entity = $level->getEntity((int)$eid);
            if($entity instanceof Entity) break;
        }
        if($entity === null){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't exist");
            return false;
        }
        if(!$entity instanceof SmaccerEntity && !$entity instanceof SmaccerHuman){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity isn't handled by Smaccer");
            return false;
        }
        $mode = array_shift($args);
        if(is_null($mode)){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> <mode> [args]");
            return false;
        }
        switch($mode){
            case "armor":
            case "clothes":
                if(!$sender instanceof Player){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only edit that in-game");
                    return false;
                }
                $entity->getArmorInventory()->setHelmet($sender->getArmorInventory()->getHelmet());
                $entity->getArmorInventory()->setChestplate($sender->getArmorInventory()->getChestplate());
                $entity->getArmorInventory()->setLeggings($sender->getArmorInventory()->getLeggings());
                $entity->getArmorInventory()->setBoots($sender->getArmorInventory()->getBoots());
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "All armor updated");
                return true;
            case "helm":
            case "helmet":
            case "head":
            case "hat":
            case "cap":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't wear armor");
                    return false;
                }
                $item = array_shift($args);
                if(is_null($item)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $item = $sender->getArmorInventory()->getHelmet();
                }else{
                    $data = explode(":", $item);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getId();
                    $item = Item::get($id, (int)($data[1] ?? 0));
                }
                $entity->getArmorInventory()->setHelmet($item);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Helmet updated");
                return true;
            case "chest":
            case "shirt":
            case "chestplate":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't wear armor");
                    return false;
                }
                $item = array_shift($args);
                if(is_null($item)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $item = $sender->getArmorInventory()->getChestplate();
                }else{
                    $data = explode(":", $item);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getId();
                    $item = Item::get($id, (int)($data[1] ?? 0));
                }
                $entity->getArmorInventory()->setChestplate($item);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Chestplate updated");
                return true;
            case "pants":
            case "legs":
            case "leggings":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't wear armor");
                    return false;
                }
                $item = array_shift($args);
                if(is_null($item)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $item = $sender->getArmorInventory()->getLeggings();
                }else{
                    $data = explode(":", $item);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getId();
                    $item = Item::get($id, (int)($data[1] ?? 0));
                }
                $entity->getArmorInventory()->setLeggings($item);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Leggings updated");
                return true;
            case "feet":
            case "boots":
            case "shoes":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't wear armor");
                    return false;
                }
                $item = array_shift($args);
                if(is_null($item)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $item = $sender->getArmorInventory()->getBoots();
                }else{
                    $data = explode(":", $item);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getId();
                    $item = Item::get($id, (int)($data[1] ?? 0));
                }
                $entity->getArmorInventory()->setBoots($item);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Boots updated");
                return true;
            case "hand":
            case "item":
            case "holding":
            case "arm":
            case "held":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't hold items");
                    return false;
                }
                $item = array_shift($args);
                if(is_null($item)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $item = $sender->getInventory()->getItemInHand();
                }else{
                    $data = explode(":", $item);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getId();
                    $item = Item::get($id, (int)($data[1] ?? 0));
                }
                $entity->getInventory()->setItemInHand($item);
                $entity->getInventory()->sendHeldItem($entity->getViewers());
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Item updated");
                return true;
            case "setskin":
            case "changeskin":
            case "editskin";
            case "skin":
                if(!$sender instanceof Player){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only edit that in-game");
                    return false;
                }
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't have a skin");
                    return false;
                }
                $entity->setSkin($sender->getSkin());
                $entity->sendData($entity->getViewers());
                $entity->respawnToAll();
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Skin updated");
                return true;
            case "name":
            case "customname":
                if(!isset($args[0])){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a name");
                    return false;
                }
                $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
                $remove = ["remove", "", "disable", "off", "hide", "none"];
                if(in_array($name, $remove)) $name = "";
                $entity->namedtag->setString(SmaccerEntity::TAG_NAME, $name);
                $entity->setNameTag($name);
                $entity->sendData($entity->getViewers());
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Name updated");
                return true;
            case "listname":
            case "nameonlist":
            case "menuname":
                if(!$entity instanceof SmaccerHuman){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity can't have menuname");
                    return false;
                }
                if(!isset($args[0])){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a menuname");
                    return false;
                }
                $type = 0;
                $input = trim(implode(" ", $args));
                $remove = ["remove", "", "disable", "off", "hide", "none"];
                if(in_array($input, $remove)) $type = 1;
                if($type === 0) $entity->namedtag->setString("MenuName", $input);else $entity->namedtag->setString("MenuName", "");
                $entity->respawnToAll();
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Menu name updated");
                return true;
            case "addc":
            case "addcmd":
            case "addcommand":
            case "delc":
            case "delcmd":
            case "delcommand":
            case "removecommand":
            case "listcommands":
            case "listcmds":
                $sender->sendMessage(Smaccer::PREFIX . TF::RED . "This command is deprecated, please use the new syntax: " . TF::AQUA . "/smaccer edit <eid> cmd");
                return false;
            case "block":
            case "tile":
            case "blockid":
            case "tileid":
                if(!$entity instanceof SmaccerFallingSand){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity isn't a block");
                    return false;
                }
                $block = array_shift($args);
                if(is_null($block)){
                    if(!$sender instanceof Player){
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You must provide an item ID");
                        return false;
                    }
                    $block = $sender->getInventory()->getItemInHand()->getBlock();
                }else{
                    $data = explode(":", $block);
                    $id = is_integer($data[0]) ? (int)$data[0] : Item::fromString((string)$data[0])->getBlock()->getId();
                    $block = Item::get($id, (int)($data[1] ?? 0))->getBlock();
                }
                $entity->getDataPropertyManager()->setInt(Entity::DATA_VARIANT, RuntimeBlockMapping::toStaticRuntimeId($block->getId(), $block->getDamage()));
                $entity->sendData($entity->getViewers());
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Block updated");
                return true;
            case "teleporthere":
            case "tphere":
            case "movehere":
            case "bringhere":
                if(!$sender instanceof Player){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only edit that in-game");
                    return false;
                }
                $entity->teleport($sender);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Teleported entity to you");
                $entity->respawnToAll();
                return true;
            case "teleportto":
            case "tpto":
            case "goto":
            case "teleport":
            case "tp":
                if(!$sender instanceof Player){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only edit that in-game");
                    return false;
                }
                $sender->teleport($entity);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Teleported you to entity");
                return true;
            case "scale":
            case "size":
                $scale = array_shift($args) ?? 1.0;
                $entity->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, (float)$scale);
                $entity->sendData($entity->getViewers());
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Updated scale");
                return true;
            case "slap":
            case "slapback":
            case "smack":
            case "swing":
            case "hit":
                $slap = array_shift($args);
                if(in_array($slap, ["off", "false", false, "no", "remove", "none", "disable"])){
                    $entity->namedtag->setByte(SmaccerEntity::TAG_SLAP, 0);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "SlapBack disabled");
                    return true;
                }elseif(in_array($slap, ["on", "true", true, "yes", "enable"])){
                    $entity->namedtag->setByte(SmaccerEntity::TAG_SLAP, 1);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "SlapBack enabled");
                    return true;
                }else{
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> slap <on|off>");
                    return false;
                }
            case "delay":
            case "cool":
            case "cooldown":
                $delay = array_shift($args) ?? Smaccer::$settings["Default"]["cooldown"];
                $remove = ["remove", "", "disable", "off", "none"];
                if(in_array($delay, $remove)){
                    $entity->namedtag->removeTag(SmaccerEntity::TAG_COOLDOWN);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Removed cooldown");
                    return true;
                }
                if(!is_numeric($delay)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Cooldown delay must be numeric");
                    return false;
                }
                $entity->namedtag->setFloat(SmaccerEntity::TAG_COOLDOWN, (float)$delay);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Updated cooldown delay");
                return true;
            case "spin":
            case "speed":
            case "rotate":
                $speed = array_shift($args) ?? 1.0;
                $remove = ["remove", "", "disable", "off", "none", "stop"];
                if(in_array($speed, $remove)){
                    $entity->namedtag->removeTag(SmaccerEntity::TAG_SPIN);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Removed rotation");
                    return true;
                }
                if(!is_numeric($speed)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Speed value must be numeric");
                    return false;
                }
                $entity->namedtag->setFloat(SmaccerEntity::TAG_SPIN, (float)$speed);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Updated rotation speed");
                return true;
            case "follow":
            case "look":
                $look = array_shift($args);
                if(in_array($look, ["off", "false", false, "no", "remove", "stop", "none", "disable"])){
                    $entity->namedtag->setByte(SmaccerEntity::TAG_ROTATE, 0);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Following disabled");
                    return true;
                }elseif(in_array($look, ["on", "true", true, "yes", "enable"])){
                    $entity->namedtag->setByte(SmaccerEntity::TAG_ROTATE, 1);
                    $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Following enabled");
                    return true;
                }else{
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> follow <on|off>");
                    return false;
                }
            case "cmd":
            case "cmds":
            case "command":
            case "commands":
                $mode = array_shift($args);
                switch($mode){
                    case "add":
                    case "new":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a command");
                            return false;
                        }
                        $input = trim(implode(" ", $args));
                        $commands = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_COMMAND) ?? new CompoundTag(SmaccerEntity::TAG_COMMAND);
                        if($commands->hasTag($input)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That command has already been added");
                            return false;
                        }
                        $commands->setString($input, $input);
                        $entity->namedtag->setTag($commands);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Command added");
                        return true;
                    case "del":
                    case "delete":
                    case "rem":
                    case "remove":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a command");
                            return false;
                        }
                        $input = trim(implode(" ", $args));
                        $commands = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_COMMAND) ?? new CompoundTag(SmaccerEntity::TAG_COMMAND);
                        if(!$commands->hasTag($input)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That command doesn't exist");
                            return false;
                        }
                        $commands->removeTag($input);
                        $entity->namedtag->setTag($commands);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Command removed");
                        return true;
                    case "list":
                    case "show":
                        $commands = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_COMMAND);
                        if($commands === null || $commands->getCount() === 0){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any commands");
                            return false;
                        }
                        $id = 0;
                        foreach($commands as $command){
                            $id++;
                            $sender->sendMessage(Smaccer::PREFIX . TF::YELLOW . "[" . TF::LIGHT_PURPLE . $id . TF::YELLOW . "] " . TF::GREEN . $command->getValue());
                        }
                        return true;
                    case "clear":
                    case "delall":
                    case "reset":
                        if($entity->namedtag->hasTag(SmaccerEntity::TAG_COMMAND)){
                            $entity->namedtag->removeTag(SmaccerEntity::TAG_COMMAND);
                            $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "All commands removed");
                            return true;
                        }else{
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any commands");
                            return false;
                        }
                    default:
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> cmd <add <name>|remove <name>|list|clear>");
                        return false;
                }
            case "world":
            case "worlds":
            case "level":
            case "levels":
                if($entity->namedtag->hasTag(SmaccerEntity::TAG_SERVER)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity already has servers attached, remove those before using worlds");
                    return false;
                }
                $mode = array_shift($args);
                switch($mode){
                    case "add":
                    case "new":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> worlds <add <name>");
                            return false;
                        }
                        $name = array_shift($args);
                        $worlds = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_WORLD) ?? new CompoundTag(SmaccerEntity::TAG_WORLD);
                        if($worlds->hasTag($name)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That world has already been added to that entity");
                            return false;
                        }
                        $worlds->setString($name, $name);
                        $entity->namedtag->setTag($worlds);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "World added");
                        return true;
                    case "del":
                    case "delete":
                    case "rem":
                    case "remove":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> worlds remove <name>");
                            return false;
                        }
                        $name = array_shift($args);
                        $worlds = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_WORLD) ?? new CompoundTag(SmaccerEntity::TAG_WORLD);
                        if(!$worlds->hasTag($name)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That world doesn't exist on that entity");
                            return false;
                        }
                        $worlds->removeTag($name);
                        $entity->namedtag->setTag($worlds);
                        if($worlds->getCount() === 0) $entity->namedtag->removeTag(SmaccerEntity::TAG_WORLD);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "World removed");
                        return true;
                    case "list":
                    case "show":
                        $worlds = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_WORLD);
                        if($worlds === null || $worlds->getCount() === 0){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any worlds added");
                            return false;
                        }
                        $id = 0;
                        foreach($worlds as $world){
                            $id++;
                            $sender->sendMessage(Smaccer::PREFIX . TF::YELLOW . "[" . TF::LIGHT_PURPLE . $id . TF::YELLOW . "] " . TF::GREEN . $world->getValue());
                        }
                        return true;
                    case "clear":
                    case "delall":
                    case "reset":
                        if($entity->namedtag->hasTag(SmaccerEntity::TAG_WORLD)){
                            $entity->namedtag->removeTag(SmaccerEntity::TAG_WORLD);
                            $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "All worlds removed");
                            return true;
                        }else{
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any worlds added");
                            return false;
                        }
                    default:
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> worlds <add <name>|remove <name>|list|clear>");
                        return false;
                }
            case "server":
            case "servers":
                if($entity->namedtag->hasTag(SmaccerEntity::TAG_WORLD)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity already has worlds attached, remove those before using servers");
                    return false;
                }
                $mode = array_shift($args);
                switch($mode){
                    case "add":
                    case "new":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> servers add <ip> [port]");
                            return false;
                        }
                        $ip = array_shift($args);
                        $port = $args[0] ?? 19132;
                        if(!is_numeric($port)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Port must be an integer");
                            return false;
                        }
                        $server = $ip . ":" . $port;
                        $servers = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER) ?? new CompoundTag(SmaccerEntity::TAG_SERVER);
                        if($servers->hasTag($server)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That server has already been added to that entity");
                            return false;
                        }
                        $servers->setString($server, $server);
                        $entity->namedtag->setTag($servers);
                        QueryManager::initServer($server);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Server added");
                        return true;
                    case "del":
                    case "delete":
                    case "rem":
                    case "remove":
                        if(!isset($args[0])){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> servers remove <ip> [port]");
                            return false;
                        }
                        $ip = array_shift($args);
                        $port = $args[0] ?? 19132;
                        $server = $ip . ":" . $port;
                        $servers = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER) ?? new CompoundTag(SmaccerEntity::TAG_SERVER);
                        if(!$servers->hasTag($server)){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That server doesn't exist on that entity");
                            return false;
                        }
                        $servers->removeTag($server);
                        $entity->namedtag->setTag($servers);
                        if($servers->getCount() === 0) $entity->namedtag->removeTag(SmaccerEntity::TAG_SERVER);
                        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Server removed");
                        return true;
                    case "list":
                    case "show":
                        $servers = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER);
                        if($servers === null || $servers->getCount() === 0){
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any servers added");
                            return false;
                        }
                        $id = 0;
                        foreach($servers as $server){
                            $id++;
                            $sender->sendMessage(Smaccer::PREFIX . TF::YELLOW . "[" . TF::LIGHT_PURPLE . $id . TF::YELLOW . "] " . TF::GREEN . $server->getValue());
                        }
                        return true;
                    case "clear":
                    case "delall":
                    case "reset":
                        if($entity->namedtag->hasTag(SmaccerEntity::TAG_SERVER)){
                            $entity->namedtag->removeTag(SmaccerEntity::TAG_SERVER);
                            $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "All servers removed");
                            return true;
                        }else{
                            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity doesn't have any servers added");
                            return false;
                        }
                    default:
                        $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Usage: /smaccer edit <eid> servers <add <ip> [port]|remove <ip> [port]|list|clear>");
                        return false;
                }
            default:
                $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Use " . TF::AQUA . "/smaccer help edit" . TF::RED . " to view all edit commands");
                return false;
        }
    }
}