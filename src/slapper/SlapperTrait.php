<?php

declare(strict_types=1);

namespace slapper;

use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\Player;
use Xenophilicy\Smaccer\entities\SmaccerEntity;

/**
 * Trait containing methods used in various Slappers.
 */
trait SlapperTrait {
    /** @var CompoundTag */
    public $namedtag;
    
    public function prepareMetadata(): void{
        $this->setGenericFlag(Entity::DATA_FLAG_IMMOBILE, true);
        if(!$this->namedtag->hasTag(SmaccerEntity::TAG_SCALE, FloatTag::class)){
            $this->namedtag->setFloat(SmaccerEntity::TAG_SCALE, 1.0, true);
        }
        $this->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $this->namedtag->getFloat(SmaccerEntity::TAG_SCALE));
    }
    
    abstract public function setGenericFlag(int $flag, bool $value = true): void;
    
    /**
     * @return DataPropertyManager
     */
    abstract public function getDataPropertyManager(): DataPropertyManager;
    
    public function tryChangeMovement(): void{
    
    }
    
    /**
     * @param $playerList
     * @param array|null $data
     */
    public function sendData($playerList, array $data = null): void{
        if(!is_array($playerList)){
            $playerList = [$playerList];
        }
        
        foreach($playerList as $p){
            $playerData = $data ?? $this->getDataPropertyManager()->getAll();
            unset($playerData[self::DATA_NAMETAG]);
            $pk = new SetEntityDataPacket();
            $pk->entityRuntimeId = $this->getId();
            $pk->metadata = $playerData;
            $p->dataPacket($pk);
            
            $this->sendNameTag($p);
        }
    }
    
    abstract public function sendNameTag(Player $player): void;
    
    public function saveSlapperNbt(): void{
        $visibility = 0;
        if($this->isNameTagVisible()){
            $visibility = 1;
            if($this->isNameTagAlwaysVisible()){
                $visibility = 2;
            }
        }
        $scale = $this->getDataPropertyManager()->getFloat(Entity::DATA_SCALE);
        $this->namedtag->setInt("NameVisibility", $visibility, true);
        $this->namedtag->setFloat(SmaccerEntity::TAG_SCALE, $scale, true);
    }
    
    public function getDisplayName(Player $player): string{
        $vars = ["{name}" => $player->getName(), "{display_name}" => $player->getName(), "{nametag}" => $player->getNameTag()];
        return str_replace(array_keys($vars), array_values($vars), $this->getNameTag());
    }
    
    /**
     * @return string
     */
    abstract public function getNameTag(): string;
}
