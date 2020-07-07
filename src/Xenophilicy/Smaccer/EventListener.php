<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\command\ConsoleCommandSender;
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
use Xenophilicy\Smaccer\entities\SlapperEntity;
use Xenophilicy\Smaccer\entities\SlapperHuman;
use Xenophilicy\Smaccer\events\SlapperCreationEvent;
use Xenophilicy\Smaccer\events\SlapperHitEvent;

/**
 * Class EventListener
 * @package Xenophilicy\Smaccer
 */
class EventListener implements Listener {
    
    /**
     * @param SlapperCreationEvent $ev
     */
    public function onSlapperCreation(SlapperCreationEvent $ev){
        if(!Smaccer::addonEnabled("SlapperCache")) return;
        if($ev->getCause() !== SlapperCreationEvent::CAUSE_COMMAND) return;
        $entity = $ev->getEntity();
        $entity->saveNBT();
        Smaccer::getInstance()->cacheHandler->storeSlapperNbt($entity->getNameTag(), $entity->getSaveId(), $entity->getLevel()->getName(), $entity->namedtag);
    }
    
    /**
     * @param SlapperHitEvent $event
     */
    public function onSlapperHit(SlapperHitEvent $event){
        if(Smaccer::addonEnabled("SlapBack")){
            $entity = $event->getEntity();
            if($entity instanceof SlapperHuman){
                $pk = new AnimatePacket();
                $pk->entityRuntimeId = $entity->getId();
                $pk->action = AnimatePacket::ACTION_SWING_ARM;
                $event->getDamager()->dataPacket($pk);
            }
        }
        if(Smaccer::addonEnabled("SlapperCooldown")){
            $name = $event->getDamager()->getName();
            if(!isset(Smaccer::getInstance()->lastHit[$name])){
                Smaccer::getInstance()->lastHit[$name] = microtime(true);
                return;
            }
            if((Smaccer::getInstance()->lastHit[$name] + Smaccer::$settings["SlapperCooldown"]["delay"]) > microtime(true)){
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
            if(isset(Smaccer::getInstance()->hitSessions[$damagerName])){
                if($entity instanceof SlapperHuman){
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
            if(($commands = $entity->namedtag->getCompoundTag("Commands")) !== null){
                $server = Smaccer::getInstance()->getServer();
                foreach($commands as $stringTag){
                    $server->dispatchCommand(new ConsoleCommandSender(), str_replace("{player}", '"' . $damagerName . '"', $stringTag->getValue()));
                }
            }
        }
    }
    
    /**
     * @param EntitySpawnEvent $event
     *
     * @return void
     */
    public function onEntitySpawn(EntitySpawnEvent $event): void{
        $entity = $event->getEntity();
        if($entity instanceof SlapperEntity || $entity instanceof SlapperHuman){
            $clearLagg = Smaccer::getInstance()->getServer()->getPluginManager()->getPlugin("ClearLagg");
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
    
    /**
     * @param PlayerMoveEvent $event
     */
    public function onPlayerMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        $from = $event->getFrom();
        $to = $event->getTo();
        if($from->distance($to) < 0.1) return;
        $maxDistance = Smaccer::$settings["SlapperRotation"]["max-distance"];
        foreach($player->getLevel()->getNearbyEntities($player->getBoundingBox()->expandedCopy($maxDistance, $maxDistance, $maxDistance), $player) as $e){
            if($e instanceof Player) continue;
            if(substr($e->getSaveId(), 0, 7) !== "Slapper") continue;
            $entities = ["SlapperFallingSand", "SlapperMinecart", "SlapperBoat", "SlapperPrimedTNT", "SlapperShulker"];
            if(in_array($e->getSaveId(), $entities)) continue;
            $xdiff = $player->x - $e->x;
            $zdiff = $player->z - $e->z;
            $angle = atan2($zdiff, $xdiff);
            $yaw = (($angle * 180) / M_PI) - 90;
            $ydiff = $player->y - $e->y;
            $v = new Vector2($e->x, $e->z);
            $dist = $v->distance($player->x, $player->z);
            $angle = atan2($dist, $ydiff);
            $pitch = (($angle * 180) / M_PI) - 90;
            if($e->getSaveId() === "SlapperHuman"){
                $pk = new MovePlayerPacket();
                $pk->entityRuntimeId = $e->getId();
                $pk->position = $e->asVector3()->add(0, $e->getEyeHeight(), 0);
                $pk->yaw = $yaw;
                $pk->pitch = $pitch;
                $pk->headYaw = $yaw;
                $pk->onGround = $e->onGround;
            }else{
                $pk = new MoveActorAbsolutePacket();
                $pk->entityRuntimeId = $e->getId();
                $pk->position = $e->asVector3();
                $pk->xRot = $pitch;
                $pk->yRot = $yaw;
                $pk->zRot = $yaw;
            }
            $player->dataPacket($pk);
        }
    }
}