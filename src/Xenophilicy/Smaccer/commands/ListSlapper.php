<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\level\Level;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SlapperEntity;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class ListSlapper
 * @package Xenophilicy\Smaccer\commands
 */
class ListSlapper extends SubSlapper {
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param array $args
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapper.remove")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to spawn Slappers");
            return false;
        }
        if(!isset($args[0])){
            $count = 0;
            $levels = [];
            foreach(Smaccer::getInstance()->getServer()->getLevels() as $level){
                $slappers = $this->getSlappers($level);
                if(count($slappers) === 0) continue;
                $count += count($slappers);
                array_push($levels, $slappers);
            }
            if($count === 0){
                $sender->sendMessage(Smaccer::PREFIX . TF::RED . "There are no Slappers to show");
                return false;
            }
            $sender->sendMessage(TF::GOLD . "--- All Slappers ---");
            foreach($levels as $slappers){
                foreach($slappers as $id => $slapper){
                    $sender->sendMessage(TF::GREEN . "[" . $id . "] " . TF::EOL . TF::LIGHT_PURPLE . "- Name: " . $slapper[0]->getNameTag() . TF::RESET . TF::EOL . TF::YELLOW . "- Type: " . $slapper[1] . TF::EOL . TF::AQUA . "- Level: " . $slapper[0]->getLevel()->getName());
                }
            }
            return true;
        }
        $name = array_shift($args);
        if(!Smaccer::getInstance()->getServer()->loadLevel($name) || !($level = Smaccer::getInstance()->getServer()->getLevelByName($name)) instanceof Level){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "That level doesn't exist");
            return false;
        }
        $slappers = $this->getSlappers($level);
        if(count($slappers) === 0){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "There are no Slappers on that level");
            return false;
        }
        $sender->sendMessage(TF::GOLD . "--- Slappers on " . TF::GREEN . $level->getName() . TF::GOLD . " ---");
        foreach($slappers as $id => $slapper){
            $sender->sendMessage(TF::GREEN . "[" . $id . "] " . TF::EOL . TF::LIGHT_PURPLE . "- Name: " . $slapper[0]->getNameTag() . TF::RESET . TF::EOL . TF::YELLOW . "- Type: " . $slapper[1]);
        }
        return true;
    }
    
    private function getSlappers(Level $level): array{
        $entities = [];
        foreach($level->getEntities() as $entity){
            if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
                $class = get_class($entity);
                if(strpos($class, "other") === false){
                    $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\Slapper"));
                }else{
                    $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\other\\Slapper"));
                }
                $entities[$entity->getId()] = [$entity, $entityType];
            }
        }
        return $entities;
    }
}