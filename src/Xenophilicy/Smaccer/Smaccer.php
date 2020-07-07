<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\commands\BaseSlapper;
use Xenophilicy\Smaccer\commands\CancelSlapper;
use Xenophilicy\Smaccer\commands\EditSlapper;
use Xenophilicy\Smaccer\commands\HelpSlapper;
use Xenophilicy\Smaccer\commands\IdSlapper;
use Xenophilicy\Smaccer\commands\RCA;
use Xenophilicy\Smaccer\commands\RemoveSlapper;
use Xenophilicy\Smaccer\commands\SlapperPlus;
use Xenophilicy\Smaccer\commands\SpawnSlapper;
use Xenophilicy\Smaccer\events\SlapperCreationEvent;

/**
 * Class Smaccer
 * @package Xenophilicy\Smaccer
 */
class Smaccer extends PluginBase implements Listener {
    
    public const PREFIX = TF::YELLOW . "[" . TF::GREEN . "Smaccer" . TF::YELLOW . "] ";
    /** @var array */
    public static $settings;
    /** @var Smaccer */
    private static $instance;
    /** @var array */
    public $hitSessions = [];
    /** @var array */
    public $idSessions = [];
    /** @var array */
    public $entityIds = [];
    /** @var array */
    public $editingId = [];
    
    public static function getInstance(): self{
        return self::$instance;
    }
    
    /**
     * @return void
     */
    public function onEnable(): void{
        self::$instance = $this;
        $this->saveDefaultConfig();
        self::$settings = $this->getConfig()->getAll();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        EntityManager::init();
        $this->enableAddons();
        $cmd = new BaseSlapper();
        $this->getServer()->getCommandMap()->register("slapper", $cmd);
        $this->getServer()->getCommandMap()->register("rca", new RCA());
        $cmd->registerSubSlapper("help", new HelpSlapper());
        $cmd->registerSubSlapper("id", new IdSlapper());
        $cmd->registerSubSlapper("edit", new EditSlapper());
        $cmd->registerSubSlapper("remove", new RemoveSlapper(), ["delete", "rm", "del"]);
        $cmd->registerSubSlapper("cancel", new CancelSlapper(), ["stopremove", "stopid", "stop"]);
        $cmd->registerSubSlapper("spawn", new SpawnSlapper(), ["add", "make", "create", "spawn", "apawn", "spanw", "new"]);
    }
    
    private function enableAddons(){
        if(self::addonEnabled("SlapperRotation")) $this->getLogger()->info("Enabled SlapperRotation");
        if(self::addonEnabled("SlapBack")) $this->getLogger()->info("Enabled SlapBack");
        if(self::addonEnabled("SlapperPlus")){
            $this->getLogger()->info("Enabled SlapperPlus");
            $this->getServer()->getCommandMap()->register("slapperplus", new SlapperPlus());
        }
    }
    
    public static function addonEnabled(string $addon): bool{
        if(self::$settings[$addon]["enabled"]) return true;
        return false;
    }
    
    /**
     * @param Player $player
     * @param int $type
     * @param string $name
     */
    public function makeSlapper(Player $player, int $type, string $name){
        $type = EntityManager::ENTITY_TYPES[$type];
        $nbt = $this->makeNBT($type, $player, $name);
        $entity = Entity::createEntity("Slapper" . $type, $player->getLevel(), $nbt);
        $entity->spawnToAll();
        $event = new SlapperCreationEvent($entity, "Slapper" . $type, $player, SlapperCreationEvent::CAUSE_COMMAND);
        $event->call();
        $entity->spawnToAll();
        $player->sendMessage(self::PREFIX . TF::GREEN . "Created {$type} entity");
    }
    
    /**
     * @param string $type
     * @param Player $player
     * @param string $name
     *
     * @return CompoundTag
     */
    public function makeNBT($type, Player $player, string $name): CompoundTag{
        $nbt = Entity::createBaseNBT($player, null, $player->getYaw(), $player->getPitch());
        $nbt->setShort("Health", 1);
        $nbt->setTag(new CompoundTag("Commands", []));
        $nbt->setString("MenuName", "");
        $nbt->setString("CustomName", $name);
        $nbt->setString("SlapperVersion", $this->getDescription()->getVersion());
        if($type === "Human"){
            $player->saveNBT();
            $inventoryTag = $player->namedtag->getListTag("Inventory");
            assert($inventoryTag !== null);
            $nbt->setTag(clone $inventoryTag);
            $skinTag = $player->namedtag->getCompoundTag("Skin");
            assert($skinTag !== null);
            $nbt->setTag(clone $skinTag);
        }
        return $nbt;
    }
}
