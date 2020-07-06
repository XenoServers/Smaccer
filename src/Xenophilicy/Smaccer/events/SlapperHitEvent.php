<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\events;

use pocketmine\entity\Entity;
use pocketmine\event\Cancellable;
use pocketmine\event\entity\EntityEvent;
use pocketmine\Player;

/**
 * Class SlapperHitEvent
 * @package Xenophilicy\Smaccer\events
 */
class SlapperHitEvent extends EntityEvent implements Cancellable {
    
    /** @var Player */
    private $damager;
    
    /**
     * SlapperHitEvent constructor.
     * @param Entity $entity
     * @param Player $damager
     */
    public function __construct(Entity $entity, Player $damager){
        $this->entity = $entity;
        $this->damager = $damager;
    }
    
    /**
     * @return Player
     */
    public function getDamager(): Player{
        return $this->damager;
    }
}
