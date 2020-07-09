<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class ListSmaccer
 * @package Xenophilicy\Smaccer\commands
 */
class ListSmaccer extends SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("smaccer.remove")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Smaccers");
            return false;
        }
        if(!isset($args[0])){
            $count = 0;
            $levels = [];
            foreach(Smaccer::getInstance()->getServer()->getLevels() as $level){
                $smaccers = $this->getSmaccers($level);
                if(count($smaccers) === 0) continue;
                $count += count($smaccers);
                array_push($levels, $smaccers);
            }
            if($count === 0){
                $sender->sendMessage(Smaccer::PREFIX . TF::RED . "There are no Smaccers to show");
                return false;
            }
            $sender->sendMessage(TF::GOLD . "--- All Smaccers ---");
            foreach($levels as $smaccers){
                foreach($smaccers as $id => $smaccer){
                    $sender->sendMessage(TF::GREEN . "[" . $id . "] " . TF::EOL . TF::LIGHT_PURPLE . "- Name: " . $smaccer[0]->getNameTag() . TF::RESET . TF::EOL . TF::YELLOW . "- Type: " . $smaccer[1] . TF::EOL . TF::AQUA . "- Level: " . $smaccer[0]->getLevel()->getName());
                }
            }
            return true;
        }
        $name = array_shift($args);
        if(!Smaccer::getInstance()->getServer()->loadLevel($name) || !($level = Smaccer::getInstance()->getServer()->getLevelByName($name)) instanceof Level){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That level doesn't exist");
            return false;
        }
        $smaccers = $this->getSmaccers($level);
        if(count($smaccers) === 0){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "There are no Smaccers on that level");
            return false;
        }
        $sender->sendMessage(TF::GOLD . "--- Smaccers on " . TF::GREEN . $level->getName() . TF::GOLD . " ---");
        foreach($smaccers as $id => $smaccer){
            $sender->sendMessage(TF::GREEN . "[" . $id . "] " . TF::EOL . TF::LIGHT_PURPLE . "- Name: " . $smaccer[0]->getNameTag() . TF::RESET . TF::EOL . TF::YELLOW . "- Type: " . $smaccer[1]);
        }
        return true;
    }
    
    private function getSmaccers(Level $level): array{
        $entities = [];
        foreach($level->getEntities() as $entity){
            if($entity instanceof SmaccerEntity || $entity instanceof SmaccerHuman){
                $class = get_class($entity);
                if(strpos($class, "other") === false){
                    $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\Smaccer"));
                }else{
                    $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\other\\Smaccer"));
                }
                $entities[$entity->getId()] = [$entity, $entityType];
            }
        }
        return $entities;
    }
}