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
                if(!isset($args[0])){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a command");
                    return false;
                }
                $input = trim(implode(" ", $args));
                $commands = $entity->namedtag->getCompoundTag("Commands") ?? new CompoundTag("Commands");
                if($commands->hasTag($input)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That command has already been added");
                    return false;
                }
                $commands->setString($input, $input);
                $entity->namedtag->setTag($commands);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Command added");
                return true;
            case "delc":
            case "delcmd":
            case "delcommand":
            case "removecommand":
                if(!isset($args[0])){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Enter a command");
                    return false;
                }
                $input = trim(implode(" ", $args));
                $commands = $entity->namedtag->getCompoundTag("Commands") ?? new CompoundTag("Commands");
                if(!$commands->hasTag($input)){
                    $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That command doesn't exist");
                    return false;
                }
                $commands->removeTag($input);
                $entity->namedtag->setTag($commands);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Command removed");
                return true;
            case "listcommands":
            case "listcmds":
            case "listcs":
                $commands = $entity->namedtag->getCompoundTag("Commands");
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
            case "spin":
            case "speed":
            case "rotate":
                $speed = array_shift($args) ?? 1.0;
                $remove = ["remove", "", "disable", "off", "none", "stop"];
                if(in_array($speed, $remove)) $speed = 0.0;
                $entity->getDataPropertyManager()->setFloat(SmaccerEntity::DATA_SPINNING, (float)$speed);
                $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . "Updated rotation speed");
                return true;
            default:
                $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Use " . TF::AQUA . "/smaccer help edit" . TF::RED . " to view all edit commands");
                return false;
        }
    }
}