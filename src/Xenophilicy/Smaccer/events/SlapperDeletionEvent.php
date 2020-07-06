<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\events;

use pocketmine\entity\Entity;
use pocketmine\event\entity\EntityEvent;

/**
 * Class SlapperDeletionEvent
 * @package Xenophilicy\Smaccer\events
 */
class SlapperDeletionEvent extends EntityEvent {
    
    /**
     * SlapperDeletionEvent constructor.
     * @param Entity $entity
     */
    public function __construct(Entity $entity){
        $this->entity = $entity;
    }
    
}
