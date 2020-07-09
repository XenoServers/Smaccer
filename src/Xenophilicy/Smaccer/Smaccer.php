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
use Xenophilicy\Smaccer\commands\BaseSmaccer;
use Xenophilicy\Smaccer\commands\CancelSmaccer;
use Xenophilicy\Smaccer\commands\EditSmaccer;
use Xenophilicy\Smaccer\commands\HelpSmaccer;
use Xenophilicy\Smaccer\commands\IdSmaccer;
use Xenophilicy\Smaccer\commands\ListSmaccer;
use Xenophilicy\Smaccer\commands\RCA;
use Xenophilicy\Smaccer\commands\RemoveSmaccer;
use Xenophilicy\Smaccer\commands\SmaccerPlus;
use Xenophilicy\Smaccer\commands\SpawnSmaccer;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\events\SmaccerCreationEvent;

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
        $cmd = new BaseSmaccer();
        $this->getServer()->getCommandMap()->register("smaccer", $cmd);
        $this->getServer()->getCommandMap()->register("rca", new RCA());
        $cmd->registerSubSmaccer("help", new HelpSmaccer());
        $cmd->registerSubSmaccer("id", new IdSmaccer());
        $cmd->registerSubSmaccer("edit", new EditSmaccer());
        $cmd->registerSubSmaccer("list", new ListSmaccer());
        $cmd->registerSubSmaccer("remove", new RemoveSmaccer(), ["delete", "rm", "del"]);
        $cmd->registerSubSmaccer("cancel", new CancelSmaccer(), ["stopremove", "stopid", "stop"]);
        $cmd->registerSubSmaccer("spawn", new SpawnSmaccer(), ["add", "make", "create", "spawn", "apawn", "spanw", "new"]);
        if(!Smaccer::addonEnabled("SmaccerCache")) return;
        
    }
    
    private function enableAddons(){
        if(self::addonEnabled("SmaccerCache")){
            $this->cacheHandler = new CacheHandlerV2();
            $legacyCacheHandler = new CacheHandlerV1();
            if($legacyCacheHandler->isValid()){
                foreach($legacyCacheHandler->uncacheSmaccers() as $cacheObject){
                    $this->cacheHandler->storeSmaccerNbt($cacheObject->name, $cacheObject->type, $cacheObject->level, $cacheObject->compoundTag);
                }
                $this->cacheHandler->setNeedsRestore($legacyCacheHandler->needsRestore());
                $legacyCacheHandler->nuke();
                $this->getLogger()->debug("successfully upgraded Smaccer storage to v2");
            }
            $this->checkForSmaccerRestore();
            $this->getLogger()->info("Enabled SmaccerCache");
        }
        if(self::addonEnabled("SmaccerRotation")) $this->getLogger()->info("Enabled SmaccerRotation");
        if(self::addonEnabled("SlapBack")) $this->getLogger()->info("Enabled SlapBack");
        if(self::addonEnabled("SmaccerCooldown")) $this->getLogger()->info("Enabled SmaccerCooldown");
        if(self::addonEnabled("SmaccerPlus")){
            $this->getServer()->getCommandMap()->register("smaccerplus", new SmaccerPlus());
            $this->getLogger()->info("Enabled SmaccerPlus");
        }
    }
    
    public static function addonEnabled(string $addon): bool{
        if(self::$settings[$addon]["enabled"]) return true;
        return false;
    }
    
    public function checkForSmaccerRestore(){
        if(!$this->cacheHandler->needsRestore()) return;
        $this->uncacheSmaccers();
        $this->cacheHandler->setNeedsRestore(false);
    }
    
    private function uncacheSmaccers(): void{
        foreach($this->cacheHandler->uncacheSmaccers() as $cacheObject){
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
            if(!$entity instanceof SmaccerHuman) continue;
            $smaccerInv = $cacheObject->compoundTag->getCompoundTag("SmaccerData");
            if(!$smaccerInv === null) continue;
            if($smaccerInv->hasTag("Armor", ListTag::class)){
                $humanArmour = $entity->getArmorInventory();
                /** @var CompoundTag $itemTag */
                foreach($smaccerInv->getListTag("Armor") ?? [] as $itemTag){
                    $humanArmour->setItem($itemTag->getByte("Slot"), Item::nbtDeserialize($itemTag));
                }
            }
            if($smaccerInv->hasTag("HeldItemIndex", ByteTag::class)){
                $entity->getInventory()->setHeldItemIndex($smaccerInv->getByte("HeldItemIndex"));
            }
            if($smaccerInv->hasTag("HeldItem", CompoundTag::class)){
                $entity->getInventory()->setItemInHand(Item::nbtDeserialize($smaccerInv->getCompoundTag("HeldItem")));
            }
        }
    }
    
    /**
     * @param Player $player
     * @param int $type
     * @param string $name
     */
    public function makeSmaccer(Player $player, int $type, string $name){
        $type = EntityManager::ENTITY_TYPES[$type];
        $nbt = $this->makeNBT($type, $player, $name);
        $entity = Entity::createEntity("Smaccer" . $type, $player->getLevel(), $nbt);
        $entity->spawnToAll();
        $event = new SmaccerCreationEvent($entity, "Smaccer" . $type, $player, SmaccerCreationEvent::CAUSE_COMMAND);
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
        $nbt->setString("SmaccerVersion", $this->getDescription()->getVersion());
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
