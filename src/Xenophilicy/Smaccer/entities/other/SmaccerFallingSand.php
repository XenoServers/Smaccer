<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\entities\other;

use pocketmine\block\BlockFactory;
use pocketmine\level\Level;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\IntTag;
use Xenophilicy\Smaccer\entities\SmaccerEntity;

/**
 * Class SmaccerFallingSand
 * @package Xenophilicy\Smaccer\entities\other
 */
class SmaccerFallingSand extends SmaccerEntity {
    
    const TYPE_ID = 66;
    const HEIGHT = 0.98;
    
    /**
     * SmaccerFallingSand constructor.
     * @param Level $level
     * @param CompoundTag $nbt
     */
    public function __construct(Level $level, CompoundTag $nbt){
        parent::__construct($level, $nbt);
        if(!$this->namedtag->hasTag("BlockID", IntTag::class)){
            $this->namedtag->setInt("BlockID", 1, true);
        }
        /** @noinspection PhpDeprecationInspection */
        $this->getDataPropertyManager()->setInt(self::DATA_VARIANT, BlockFactory::toStaticRuntimeId($this->namedtag->getInt("BlockID")));
    }
    
    public function saveNBT(): void{
        parent::saveNBT();
        $this->namedtag->setInt("BlockID", $this->getDataPropertyManager()->getInt(self::DATA_VARIANT), true);
    }
    
}
