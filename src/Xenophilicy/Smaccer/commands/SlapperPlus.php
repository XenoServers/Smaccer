<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\entity\Entity;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SlapperEntity;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\EntityManager;
use Xenophilicy\Smaccer\libs\jojoe77777\FormAPI\CustomForm;
use Xenophilicy\Smaccer\libs\jojoe77777\FormAPI\SimpleForm;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class SlapperPlus
 * @package Xenophilicy\Smaccer\commands
 */
class SlapperPlus extends PluginCommand {
    
    const IMAGE_URL = "https://raw.githubusercontent.com/jojoe77777/vanilla-textures/mob-heads/{name}.png";
    
    public function __construct(){
        parent::__construct("slapperplus", Smaccer::getInstance());
        $this->setPermission("slapperplus.command");
        $this->setDescription("Manage Slapper entities with a UI");
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(!$sender->hasPermission("slapperplus.command")){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You don't have permission to manage Slappers");
            return false;
        }
        if(!$sender instanceof Player){
            $sender->sendMessage(Smaccer::PREFIX . TF::RED . "You can only manage Slappers in-game");
            return false;
        }
        $this->createMenu($sender);
        return true;
    }
    
    /**
     * @param Player $player
     */
    private function createMenu(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            $selection = $data;
            if($selection === null){
                return;
            }
            switch($selection){
                case 0:
                    $this->createSlapperList($player);
                    break;
                case 1:
                    $this->createSlapperCreationForm($player);
                    break;
            }
        });
        $form->setTitle(TF::DARK_BLUE . "Main menu");
        $form->addButton(TF::GOLD . "Edit Slapper entities");
        $form->addButton(TF::GOLD . "Create a new Slapper entity");
        $player->sendForm($form);
    }
    
    /**
     * @param Player $player
     */
    private function createSlapperList(Player $player){
        $form = new SimpleForm(function(Player $player, $data){
            $selection = $data;
            if($selection === null){
                return;
            }
            $entityIds = Smaccer::getInstance()->entityIds[$player->getName()] ?? null;
            if($entityIds === null){
                $player->sendMessage(Smaccer::PREFIX . TF::RED . "Invalid form");
                return;
            }
            $eid = $entityIds[$selection] ?? null;
            if($eid === null){
                $player->sendMessage(Smaccer::PREFIX . TF::RED . "Invalid selection");
                return;
            }
            $entity = Smaccer::getInstance()->getServer()->findEntity($eid);
            unset(Smaccer::getInstance()->entityIds[$player->getName()]);
            if($entity === null || $entity->isClosed()){
                $player->sendMessage(Smaccer::PREFIX . TF::RED . "Invalid entity");
                return;
            }
            Smaccer::getInstance()->editingId[$player->getName()] = $eid;
            $this->createSlapperDesc($player, $entity);
        });
        $form->setTitle(TF::GREEN . "All Slappers");
        $form->setContent(TF::LIGHT_PURPLE . "Tap to edit");
        $entityIds = [];
        $i = 0;
        foreach($this->getPlugin()->getServer()->getLevels() as $level){
            foreach($level->getEntities() as $entity){
                if($entity instanceof SlapperEntity){
                    $class = get_class($entity);
                    if(strpos($class, "other") === false){
                        $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\Slapper"));
                    }else{
                        $entityType = substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\other\\Slapper"));
                    }
                    $form->addButton($this->formatSlapperEntity($entity, $entityType), 1, $this->getSlapperIcon($entityType));
                    $entityIds[$i] = $entity->getId();
                    ++$i;
                }elseif($entity instanceof SlapperHuman){
                    $form->addButton($this->formatSlapperHuman($entity), 1, $this->getSlapperIcon("Human"));
                    $entityIds[$i] = $entity->getId();
                    ++$i;
                }
            }
        }
        Smaccer::getInstance()->entityIds[$player->getName()] = $entityIds;
        $player->sendForm($form);
    }
    
    /**
     * @param Player $player
     * @param Entity $entity
     */
    private function createSlapperDesc(Player $player, Entity $entity){
        $form = new CustomForm(function(Player $player, $data){
            if($data === null){
                return;
            }
            $eid = Smaccer::getInstance()->editingId[$player->getName()];
            $entity = Smaccer::getInstance()->getServer()->findEntity($eid);
            if($entity === null || $entity->isClosed()){
                return;
            }
            $name = (string)$data[1];
            $yaw = (int)$data[2];
            $pitch = (int)$data[3];
            $teleport = (bool)$data[4];
            $entity->setNameTag($name);
            if($teleport){
                $entity->teleport($player);
                $entity->respawnToAll();
            }else{
                $entity->setRotation($yaw, $pitch);
            }
            $player->sendMessage(Smaccer::PREFIX . TF::GREEN . "Updated entity data");
            unset(Smaccer::getInstance()->editingId[$player->getName()]);
        });
        $form->setTitle(TF::DARK_BLUE . "Editing {$this->shortenName($entity->getNameTag())}");
        if($entity instanceof SlapperEntity) $form->addLabel(TF::LIGHT_PURPLE . "Entity type: {$this->getSlapperType($entity)}");else $form->addLabel(TF::LIGHT_PURPLE . "Entity type: Human");
        $form->addInput(TF::GOLD . "Entity name", "Name", $entity->getNameTag());
        $form->addSlider(TF::GOLD . "Yaw", 0, 360, -1, (int)$entity->getYaw());
        $form->addSlider(TF::GOLD . "Pitch", 0, 180, -1, (int)$entity->getPitch());
        $form->addToggle(TF::GOLD . "Teleport here", false);
        $player->sendForm($form);
    }
    
    /**
     * @param string $name
     * @return string
     */
    private function shortenName(string $name){
        if(strlen($name) > 16){
            return substr($name, 0, 16) . "...";
        }
        return $name;
    }
    
    /**
     * @param SlapperEntity $entity
     * @return false|string
     */
    private function getSlapperType(SlapperEntity $entity){
        $class = get_class($entity);
        if(strpos($class, "other") === false){
            return substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\Slapper"));
        }else{
            return substr(get_class($entity), strlen("Xenophilicy\\Smaccer\\entities\\other\\Slapper"));
        }
    }
    
    private function formatSlapperEntity(SlapperEntity $entity, string $type): string{
        $name = $this->shortenName($entity->getNameTag());
        $pos = round($entity->getX()) . ", " . round($entity->getY()) . ", " . round($entity->getZ()) . ", " . $entity->getLevel()->getName();
        return TF::GOLD . "'" . TF::WHITE . $name . TF::RESET . TF::GOLD . "' " . TF::LIGHT_PURPLE . "(" . $type . ")" . TF::EOL . TF::DARK_BLUE . $pos;
    }
    
    /**
     * @param $entityType
     * @return string|string[]
     */
    private function getSlapperIcon($entityType){
        if($entityType === "Human"){
            return str_replace("{name}", (mt_rand(0, 1) === 0 ? "steve" : "alex"), self::IMAGE_URL);
        }
        return str_replace("{name}", strtolower($entityType), self::IMAGE_URL);
    }
    
    private function formatSlapperHuman(SlapperHuman $entity): string{
        $name = $this->shortenName($entity->getNameTag());
        $pos = round($entity->getX()) . ", " . round($entity->getY()) . ", " . round($entity->getZ()) . ", " . $entity->getLevel()->getName();
        return TF::GOLD . "'" . TF::WHITE . $name . TF::RESET . TF::GOLD . "' " . TF::LIGHT_PURPLE . "(Human)" . TF::EOL . TF::DARK_BLUE . $pos;
    }
    
    /**
     * @param Player $player
     */
    private function createSlapperCreationForm(Player $player){
        $form = new CustomForm(function(Player $player, $data){
            if($data === null){
                return;
            }
            $entityType = $data[0];
            $name = $data[1];
            Smaccer::getInstance()->makeSlapper($player, $entityType, $name);
        });
        $form->setTitle(TF::DARK_BLUE . "Create Slapper");
        $form->addDropdown(TF::GOLD . "Entity type", EntityManager::ENTITY_TYPES, 0);
        $form->addInput(TF::GOLD . "Name", "Name", $player->getName());
        $player->sendForm($form);
    }
    
}
