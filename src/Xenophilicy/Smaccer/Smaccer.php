<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\Entity;
use pocketmine\event\Listener;
use pocketmine\item\Item;
use pocketmine\nbt\tag\ByteTag;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\cache\CacheHandlerV1;
use Xenophilicy\Smaccer\cache\CacheHandlerV2;
use Xenophilicy\Smaccer\commands\BaseSlapper;
use Xenophilicy\Smaccer\commands\CancelSlapper;
use Xenophilicy\Smaccer\commands\EditSlapper;
use Xenophilicy\Smaccer\commands\HelpSlapper;
use Xenophilicy\Smaccer\commands\IdSlapper;
use Xenophilicy\Smaccer\commands\ListSlapper;
use Xenophilicy\Smaccer\commands\RCA;
use Xenophilicy\Smaccer\commands\RemoveSlapper;
use Xenophilicy\Smaccer\commands\SlapperPlus;
use Xenophilicy\Smaccer\commands\SpawnSlapper;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\events\SlapperCreationEvent;

/**
 * Class Smaccer
 * @package Xenophilicy\Smaccer
 */
class Smaccer extends PluginBase implements Listener {
    
    public const CONFIG_VERSION = "1.2.0";
    
    public const PREFIX = TF::YELLOW . "[" . TF::GREEN . "Smaccer" . TF::YELLOW . "] ";
    /** @var array */
    public static $settings;
    /** @var Smaccer */
    private static $instance;
    /** @var array */
    public $lastHit = [];
    /** @var array */
    public $hitSessions = [];
    /** @var array */
    public $idSessions = [];
    /** @var array */
    public $entityIds = [];
    /** @var array */
    public $editingId = [];
    /** @var CacheHandlerV2 */
    public $cacheHandler;
    /** @var CacheHandlerV1 */
    public $legacyCacheHandler;
    
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
        $configVersion = self::$settings["VERSION"];
        $pluginVersion = $this->getDescription()->getVersion();
        if(version_compare(self::CONFIG_VERSION, $configVersion, "gt")){
            $this->getLogger()->warning("You've updated Smaccer to v" . $pluginVersion . " which requires a config version greater than " . self::CONFIG_VERSION . " but you have a config from v" . $configVersion . "! Please delete your old config for new features to be enabled and to prevent unwanted errors!");
            $this->getServer()->getPluginManager()->disablePlugin($this);
            return;
        }
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        EntityManager::init();
        $this->enableAddons();
        $cmd = new BaseSlapper();
        $this->getServer()->getCommandMap()->register("slapper", $cmd);
        $this->getServer()->getCommandMap()->register("rca", new RCA());
        $cmd->registerSubSlapper("help", new HelpSlapper());
        $cmd->registerSubSlapper("id", new IdSlapper());
        $cmd->registerSubSlapper("edit", new EditSlapper());
        $cmd->registerSubSlapper("list", new ListSlapper());
        $cmd->registerSubSlapper("remove", new RemoveSlapper(), ["delete", "rm", "del"]);
        $cmd->registerSubSlapper("cancel", new CancelSlapper(), ["stopremove", "stopid", "stop"]);
        $cmd->registerSubSlapper("spawn", new SpawnSlapper(), ["add", "make", "create", "spawn", "apawn", "spanw", "new"]);
        if(!Smaccer::addonEnabled("SlapperCache")) return;
        
    }
    
    private function enableAddons(){
        if(self::addonEnabled("SlapperCache")){
            $this->cacheHandler = new CacheHandlerV2();
            $legacyCacheHandler = new CacheHandlerV1();
            if($legacyCacheHandler->isValid()){
                foreach($legacyCacheHandler->uncacheSlappers() as $cacheObject){
                    $this->cacheHandler->storeSlapperNbt($cacheObject->name, $cacheObject->type, $cacheObject->level, $cacheObject->compoundTag);
                }
                $this->cacheHandler->setNeedsRestore($legacyCacheHandler->needsRestore());
                $legacyCacheHandler->nuke();
                $this->getLogger()->debug("successfully upgraded Slapper storage to v2");
            }
            $this->checkForSlapperRestore();
            $this->getLogger()->info("Enabled SlapperCache");
        }
        if(self::addonEnabled("SlapperRotation")) $this->getLogger()->info("Enabled SlapperRotation");
        if(self::addonEnabled("SlapBack")) $this->getLogger()->info("Enabled SlapBack");
        if(self::addonEnabled("SlapperCooldown")) $this->getLogger()->info("Enabled SlapperCooldown");
        if(self::addonEnabled("SlapperPlus")){
            $this->getServer()->getCommandMap()->register("slapperplus", new SlapperPlus());
            $this->getLogger()->info("Enabled SlapperPlus");
        }
    }
    
    public static function addonEnabled(string $addon): bool{
        if(self::$settings[$addon]["enabled"]) return true;
        return false;
    }
    
    public function checkForSlapperRestore(){
        if(!$this->cacheHandler->needsRestore()) return;
        $this->uncacheSlappers();
        $this->cacheHandler->setNeedsRestore(false);
    }
    
    private function uncacheSlappers(): void{
        foreach($this->cacheHandler->uncacheSlappers() as $cacheObject){
            $level = $this->getServer()->getLevelByName($cacheObject->level);
            if($level === null){
                $this->getLogger()->error(__FUNCTION__ . ": failed to restore $cacheObject->name, type $cacheObject->type, world $cacheObject->level because world is not loaded");
                continue;
            }
            $this->getLogger()->debug(__FUNCTION__ . " Processing $cacheObject->name, type $cacheObject->type, world $cacheObject->level");
            $nbt = $cacheObject->compoundTag;
            if(!$nbt->hasTag("Motion", ListTag::class)){
                $motion = new ListTag("Motion", [new DoubleTag("", 0.0), new DoubleTag("", 0.0), new DoubleTag("", 0.0)]);
                $nbt->setTag($motion);
            }
            $entity = Entity::createEntity($cacheObject->type, $level, $nbt);
            $entity->setNameTag(str_replace("Â", "", $cacheObject->name));
            $entity->setNameTagAlwaysVisible();
            $entity->setNameTagVisible();
            if(!$entity instanceof SlapperHuman) continue;
            $slapperInv = $cacheObject->compoundTag->getCompoundTag("SlapperData");
            if(!$slapperInv === null) continue;
            if($slapperInv->hasTag("Armor", ListTag::class)){
                $humanArmour = $entity->getArmorInventory();
                /** @var CompoundTag $itemTag */
                foreach($slapperInv->getListTag("Armor") ?? [] as $itemTag){
                    $humanArmour->setItem($itemTag->getByte("Slot"), Item::nbtDeserialize($itemTag));
                }
            }
            if($slapperInv->hasTag("HeldItemIndex", ByteTag::class)){
                $entity->getInventory()->setHeldItemIndex($slapperInv->getByte("HeldItemIndex"));
            }
            if($slapperInv->hasTag("HeldItem", CompoundTag::class)){
                $entity->getInventory()->setItemInHand(Item::nbtDeserialize($slapperInv->getCompoundTag("HeldItem")));
            }
        }
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
