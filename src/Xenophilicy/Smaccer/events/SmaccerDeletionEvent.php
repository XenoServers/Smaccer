<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\events;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityEvent;

/**
 * Class SmaccerDeletionEvent
 * @package Xenophilicy\Smaccer\events
 */
class SmaccerDeletionEvent extends EntityEvent {
    
    /**
     * SmaccerDeletionEvent constructor.
     * @param Entity $entity
     */
    public function __construct(Entity $entity){
        $this->entity = $entity;
    }
    
}
