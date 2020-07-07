<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\cache;

use pocketmine\nbt\tag\CompoundTag;

/**
 * Class CacheObject
 * @package Xenophilicy\Smaccer\cache
 */
class CacheObject {
    
    /** @var string */
    public $name;
    /** @var string */
    public $type;
    /** @var string */
    public $level;
    /** @var CompoundTag */
    public $compoundTag;
    
    /**
     * CacheObject constructor.
     * @param string $name
     * @param string $type
     * @param string $level
     * @param CompoundTag $tag
     */
    public function __construct(string $name, string $type, string $level, CompoundTag $tag){
        $this->name = $name;
        $this->type = $type;
        $this->level = $level;
        $this->compoundTag = $tag;
    }
}
