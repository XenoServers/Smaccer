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
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\math\Vector2;
use pocketmine\network\mcpe\protocol\AnimatePacket;
use pocketmine\network\mcpe\protocol\MoveActorAbsolutePacket;
use pocketmine\network\mcpe\protocol\MovePlayerPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use slapper\entities\other\SlapperFallingSand;
use slapper\entities\SlapperEntity;
use slapper\entities\SlapperHuman;
use Xenophilicy\Smaccer\entities\SmaccerEntity;
use Xenophilicy\Smaccer\entities\SmaccerHuman;
use Xenophilicy\Smaccer\events\SmaccerCreationEvent;
use Xenophilicy\Smaccer\events\SmaccerHitEvent;

/**
 * Class EventListener
 * @package Xenophilicy\Smaccer
 */
class EventListener implements Listener {
    
    /**
     * @param SmaccerCreationEvent $ev
     */
    public function onSmaccerCreation(SmaccerCreationEvent $ev){
        if(!Smaccer::addonEnabled("SlapperCache")) return;
        if($ev->getCause() !== SmaccerCreationEvent::CAUSE_COMMAND) return;
        $entity = $ev->getEntity();
        $entity->saveNBT();
        Smaccer::getInstance()->cacheHandler->storeSmaccerNbt($entity->getNameTag(), $entity->getSaveId(), $entity->getLevel()->getName(), $entity->namedtag);
    }
    
    /**
     * @param SmaccerHitEvent $event
     */
    public function onSmaccerHit(SmaccerHitEvent $event){
        $name = $event->getDamager()->getName();
        if(isset(Smaccer::getInstance()->hitSessions[$name])) return;
        $entity = $event->getEntity();
        if(Smaccer::addonEnabled("SlapBack")){
            $slap = $entity->namedtag->hasTag(SmaccerEntity::TAG_SLAP) ? $entity->namedtag->getByte(SmaccerEntity::TAG_SLAP) : $cooldown = Smaccer::$settings["Default"]["slap"];
            if(($slap === true || $slap === 1) && $entity instanceof SmaccerHuman){
                $pk = new AnimatePacket();
                $pk->entityRuntimeId = $entity->getId();
                $pk->action = AnimatePacket::ACTION_SWING_ARM;
                $event->getDamager()->dataPacket($pk);
            }
        }
        if(Smaccer::addonEnabled("SlapperCooldown")){
            if(!isset(Smaccer::getInstance()->lastHit[$name])){
                Smaccer::getInstance()->lastHit[$name] = microtime(true);
                return;
            }
            $cooldown = $entity->namedtag->hasTag(SmaccerEntity::TAG_COOLDOWN) ? $entity->namedtag->getFloat(SmaccerEntity::TAG_COOLDOWN) : $cooldown = Smaccer::$settings["Default"]["cooldown"];
            if(($cooldown + Smaccer::getInstance()->lastHit[$name]) > microtime(true)){
                $event->setCancelled();
                $event->getDamager()->sendTip(Smaccer::$settings["SlapperCooldown"]["message"]);
            }else{
                Smaccer::getInstance()->lastHit[$name] = microtime(true);
            }
        }
    }
    
    /**
     * @param PlayerQuitEvent $event
     */
    public function onPlayerQuit(PlayerQuitEvent $event){
        if(Smaccer::addonEnabled("SlapperPlus")){
            unset(Smaccer::getInstance()->entityIds[$event->getPlayer()->getName()]);
            unset(Smaccer::getInstance()->editingId[$event->getPlayer()->getName()]);
        }
        if(Smaccer::addonEnabled("SlapperCooldown")){
            unset(Smaccer::getInstance()->lastHit[$event->getPlayer()->getName()]);
        }
    }
    
    /**
     * @param EntityDamageEvent $event
     * @ignoreCancelled true
     * @return void
     */
    public function onEntityDamage(EntityDamageEvent $event): void{
        $entity = $event->getEntity();
        if(!$entity instanceof SmaccerEntity && !$entity instanceof SmaccerHuman) return;
        $event->setCancelled(true);
        if(!$event instanceof EntityDamageByEntityEvent) return;
        $damager = $event->getDamager();
        if(!$damager instanceof Player) return;
        $event = new SmaccerHitEvent($entity, $damager);
        $event->call();
        if($event->isCancelled()) return;
        $damagerName = $damager->getName();
        if(isset(Smaccer::getInstance()->hitSessions[$damagerName])){
            if($entity instanceof SmaccerHuman){
                $entity->getInventory()->clearAll();
            }
            $entity->close();
            unset(Smaccer::getInstance()->hitSessions[$damagerName]);
            $damager->sendMessage(Smaccer::PREFIX . TF::GREEN . "Entity removed");
            return;
        }
        if(isset(Smaccer::getInstance()->idSessions[$damagerName])){
            $damager->sendMessage(Smaccer::PREFIX . TF::GREEN . "Entity ID: " . $entity->getId());
            unset(Smaccer::getInstance()->idSessions[$damagerName]);
            return;
        }
        if(($commands = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_COMMAND)) !== null){
            $server = Smaccer::getInstance()->getServer();
            foreach($commands as $stringTag){
                $server->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", '"' . $damagerName . '"', $stringTag->getValue()));
            }
        }
    }
    
    /**
     * @param EntitySpawnEvent $event
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
            Smaccer::getInstance()->getLogger()->notice("Converting Slapper entity to Smaccer entity...");
            $entity->flagForDespawn();
            $class = get_class($entity);
            if($entity instanceof SlapperHuman) $type = "Human";else{
                if(strpos($class, "other") === false) $type = substr(get_class($entity), strlen("slapper\\entities\\slapper"));else $type = substr(get_class($entity), strlen("slapper\\entities\\other\\slapper"));
            }
            $name = $entity->getNameTag();
            $oldnbt = $entity->namedtag;
            $nbt = Entity::createBaseNBT($entity, null, $entity->getYaw(), $entity->getPitch());
            $nbt->setShort("Health", 1);
            $cmds = $oldnbt->getCompoundTag(SmaccerEntity::TAG_COMMAND);
            $nbt->setTag($cmds);
            $nbt->setByte(SmaccerEntity::TAG_ROTATE, 0);
            $nbt->setString("MenuName", "");
            $nbt->setString(SmaccerEntity::TAG_NAME, $name);
            $nbt->setString("CustomName", $name);
            $nbt->setString("SmaccerVersion", Smaccer::getInstance()->getDescription()->getVersion());
            $nbt->setFloat(SmaccerEntity::TAG_SCALE, $oldnbt->getFloat(SmaccerEntity::TAG_SCALE));
            if($entity instanceof SlapperFallingSand) $nbt->setInt(SmaccerEntity::TAG_BLOCKID, $oldnbt->getInt(SmaccerEntity::TAG_BLOCKID));
            if($type === "Human"){
                $entity->saveNBT();
                $inventoryTag = $entity->namedtag->getListTag("Inventory");
                assert($inventoryTag !== null);
                $nbt->setTag(clone $inventoryTag);
                $skinTag = $entity->namedtag->getCompoundTag("Skin");
                assert($skinTag !== null);
                $nbt->setTag(clone $skinTag);
            }
            $newEntity = Entity::createEntity("Smaccer" . $type, $entity->getLevel(), $nbt);
            $event = new SmaccerCreationEvent($newEntity, "Smaccer" . $type, null, SmaccerCreationEvent::CAUSE_COMMAND);
            $event->call();
            Smaccer::getInstance()->getLogger()->notice("Conversion successful");
            return;
        }
        if($entity instanceof SmaccerEntity || $entity instanceof SmaccerHuman){
            $entity->namedtag->setByte(SmaccerEntity::TAG_ROTATE, 0);
            $clearLagg = Smaccer::getInstance()->getServer()->getPluginManager()->getPlugin("ClearLagg");
            if($clearLagg !== null){
                /** @noinspection PhpUndefinedMethodInspection */
                $clearLagg->exemptEntity($entity);
            }
            if($entity->namedtag->hasTag(SmaccerEntity::TAG_SERVER)){
                $servers = $entity->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER);
                if($servers === null || $servers->getCount() === 0) return;
                foreach($servers as $server) QueryManager::initServer($server->getValue());
            }
        }
    }
    
    /**
     * @param EntityMotionEvent $event
     * @return void
     */
    public function onEntityMotion(EntityMotionEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof SmaccerEntity || $entity instanceof SmaccerHuman){
            $event->setCancelled(true);
        }
    }
    
    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();
        if($from->distance($to) < 0.1) return;
        $maxDistance = Smaccer::$settings["SlapperRotation"]["max-distance"];
        foreach($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player) as $entity){
            if($entity instanceof Player) continue;
            if(substr($entity->getSaveId(), 0, 7) !== "Smaccer") continue;
            if($entity->namedtag->hasTag(SmaccerEntity::TAG_SPIN)) continue;
            if($entity->namedtag->hasTag(SmaccerEntity::TAG_ROTATE)){
                if($entity->namedtag->getByte(SmaccerEntity::TAG_ROTATE) === 0 && !Smaccer::addonEnabled("SlapperRotation")) continue;
            }elseif(!Smaccer::addonEnabled("SlapperRotation")) continue;
            if(in_array($entity->getSaveId(), ["SmaccerFallingSand", "SmaccerMinecart", "SmaccerBoat", "SmaccerPrimedTNT", "SmaccerShulker"])) continue;
            $xdiff = $player->x - $entity->x;
            $zdiff = $player->z - $entity->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $player->y - $entity->y;
            $v = new Vector2($entity->x, $entity->z);
            $dist = $v->distance($player->x, $player->z);
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;
            if($entity->getSaveId() === "SmaccerHuman"){
                $pk = new MovePlayerPacket();
                $pk->entityRuntimeId = $entity->getId();
                $pk->position = $entity->asVector3()->add(0, $entity->getEyeHeight(), 0);
                $pk->yaw = $yaw;
                $pk->pitch = $pitch;
                $pk->headYaw = $yaw;
                $pk->onGround = $entity->onGround;
            }else{
                $pk = new MoveActorAbsolutePacket();
                $pk->entityRuntimeId = $entity->getId();
                $pk->position = $entity->asVector3();
                $pk->xRot = $pitch;
                $pk->yRot = $yaw;
                $pk->zRot = $yaw;
            }
            $player->dataPacket($pk);
        }
    }
}