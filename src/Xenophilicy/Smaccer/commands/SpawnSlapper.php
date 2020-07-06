<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\EntityManager;
use Xenophilicy\Smaccer\events\SlapperCreationEvent;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SpawnSlapper
 * @package Xenophilicy\Smaccer\commands
 */
class SpawnSlapper extends SubSlapper {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapper.create")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Slappers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only create Slappers in-game");
            return false;
        }
        $type = array_shift($args);
        if($type === null || empty(trim($type))){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "Please enter an entity type");
            return false;
        }
        $name = str_replace(["{color}", "{line}"], ["§", "\n"], trim(implode(" ", $args)));
        if(empty($name)){
            $name = $sender->getDisplayName();
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
        $entity = Entity::createEntity("Slapper" . $chosenType, $sender->getLevel(), $nbt);
        $event = new SlapperCreationEvent($entity, "Slapper" . $chosenType, $sender, SlapperCreationEvent::CAUSE_COMMAND);
        $event->call();
        $entity->spawnToAll();
        $sender->sendMessage(Smaccer::PREFIX . TF::GREEN . $chosenType . " entity spawned with name " . TF::WHITE . "'" . TF::BLUE . $name . TF::WHITE . "'" . TF::GREEN . " and entity ID " . TF::BLUE . $entity->getId());
        return true;
    }
}