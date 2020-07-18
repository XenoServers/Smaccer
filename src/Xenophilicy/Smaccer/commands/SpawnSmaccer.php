<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\EntityManager;
use Xenophilicy\Smaccer\events\SmaccerCreationEvent;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SpawnSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class SpawnSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.create")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Smaccers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only create Smaccers in-game");
            return false;
        }
        $type = array_shift($args);
        if($type === null || empty(trim($type))){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Please enter an entity type");
            return false;
        }
        $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
        if(empty($name)){
            $name = str_replace("{player}", $sender->getDisplayName(), Smaccer::$settings["Default"]["spawn-name"]);
        }
        $types = EntityManager::ENTITY_TYPES;
        $aliases = EntityManager::ENTITY_ALIASES;
        $chosenType = null;
        foreach($types as $t){
            if(strtolower($type) === strtolower($t)){
                $chosenType = $t;
            }
        }
        if($chosenType === null){
            foreach($aliases as $alias => $t){
                if(strtolower($type) === strtolower($alias)){
                    $chosenType = $t;
                }
            }
        }
        if($chosenType === null){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That entity is invalid");
            return false;
        }
        $nbt = Smaccer::getInstance()->makeNBT($chosenType, $sender, $name);
        $entity = Entity::createEntity("Smaccer" . $chosenType, $sender->getLevel(), $nbt);
        $event = new SmaccerCreationEvent($entity, "Smaccer" . $chosenType, $sender, SmaccerCreationEvent::CAUSE_COMMAND);
        $event->call();
        $entity->spawnToAll();
        if($entity instanceof SmaccerHuman){
            $item = $sender->getInventory()->getItemInHand();
            $entity->getInventory()->setItemInHand($item);
            $entity->getInventory()->sendHeldItem($entity->getViewers());
        }
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . $chosenType . " entity spawned with name " . TF::WHITE . "'" . TF::BLUE . $name . TF::WHITE . "'" . TF::GREEN . " and entity ID " . TF::BLUE . $entity->getId());
        return true;
    }
}