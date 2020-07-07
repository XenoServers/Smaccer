<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\command\ConsoleCommandSender;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityMotionEvent;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\AnimatePacket;
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
use Xenophilicy\Smaccer\entities\SlapperEntity;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\events\SlapperCreationEvent;
use Xenophilicy\Smaccer\events\SlapperHitEvent;

/**
 * Class Smaccer
 * @package Xenophilicy\Smaccer
 */
class Smaccer extends PluginBase implements Listener {
    
    public const PREFIX = TF::YELLOW . "[" . TF::GREEN . "Smaccer" . TF::YELLOW . "] ";
    
    /** @var Smaccer */
    private static $instance;
    /** @var array */
    private static $enabled;
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
        EntityManager::init();
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $cmd = new BaseSlapper();
        $this->getServer()->getCommandMap()->register("slapper", $cmd);
        $this->getServer()->getCommandMap()->register("rca", new RCA());
        $cmd->registerSubSlapper("help", new HelpSlapper());
        $cmd->registerSubSlapper("id", new IdSlapper());
        $cmd->registerSubSlapper("edit", new EditSlapper());
        $cmd->registerSubSlapper("remove", new RemoveSlapper(), ["delete", "rm", "del"]);
        $cmd->registerSubSlapper("cancel", new CancelSlapper(), ["stopremove", "stopid", "stop"]);
        $cmd->registerSubSlapper("spawn", new SpawnSlapper(), ["add", "make", "create", "spawn", "apawn", "spanw", "new"]);
        self::$enabled = $this->getConfig()->getAll();
        if(self::$enabled["SlapBack"]) $this->getLogger()->info("Enabled SlapBack");
        if(self::$enabled["SlapperPlus"]){
            $this->getLogger()->info("Enabled SlapperPlus");
            $this->getServer()->getCommandMap()->register("slapperplus", new SlapperPlus());
        }
    }
    
    /**
     * @param SlapperHitEvent $ev
     */
    public function onSlapperHit(SlapperHitEvent $ev){
        if(!self::$enabled["SlapBack"]) return;
        $entity = $ev->getEntity();
        if(!$entity instanceof SlapperHuman){
            return;
        }
        $pk = new AnimatePacket();
        $pk->entityRuntimeId = $entity->getId();
        $pk->action = AnimatePacket::ACTION_SWING_ARM;
        $ev->getDamager()->dataPacket($pk);
    }
    
    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
        if(!self::$enabled["SlapperPlus"]) return;
        unset($this->entityIds[$event->getPlayer()->getName()]);
        unset($this->editingId[$event->getPlayer()->getName()]);
    }
    
    /**
     * @param EntityDamageEvent $event
     *
     * @ignoreCancelled true
     *
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
            $event->setCancelled(true);
            if(!$event instanceof EntityDamageByEntityEvent){
                return;
            }
            $damager = $event->getDamager();
            if(!$damager instanceof Player){
                return;
            }
            $event = new SlapperHitEvent($entity, $damager);
            $event->call();
            if($event->isCancelled()){
                return;
            }
            $damagerName = $damager->getName();
            if(isset($this->hitSessions[$damagerName])){
                if($entity instanceof SlapperHuman){
                    $entity->getInventory()->clearAll();
                }
                $entity->close();
                unset($this->hitSessions[$damagerName]);
                $damager->sendMessage(self::PREFIX . TF::GREEN . "Entity removed");
                return;
            }
            if(isset($this->idSessions[$damagerName])){
                $damager->sendMessage(self::PREFIX . TF::GREEN . "Entity ID: " . $entity->getId());
                unset($this->idSessions[$damagerName]);
                return;
            }
            if(($commands = $entity->namedtag->getCompoundTag("Commands")) !== null){
                $server = $this->getServer();
                foreach($commands as $stringTag){
                    $server->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", '"' . $damagerName . '"', $stringTag->getValue()));
                }
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
    
    /**
     * @param EntitySpawnEvent $ev
     *
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $ev): void{
        $entity = $ev->getEntity();
        if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
            $clearLagg = $this->getServer()->getPluginManager()->getPlugin("ClearLagg");
            if($clearLagg !== null){
                /** @noinspection PhpUndefinedMethodInspection */
                $clearLagg->exemptEntity($entity);
            }
        }
    }
    
    /**
     * @param EntityMotionEvent $event
     *
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
            $event->setCancelled(true);
        }
    }
}
