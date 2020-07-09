<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\Player;

/**
 * Trait containing methods used in various Smaccers.
 */
trait SmaccerTrait {
    /** @var CompoundTag */
    public $namedtag;
    
    public function prepareMetadata(): void{
        $this->setGenericFlag(Entity::DATA_FLAG_IMMOBILE, true);
        if(!$this->namedtag->hasTag("Scale", FloatTag::class)){
            $this->namedtag->setFloat("Scale", 1.0, true);
        }
        $this->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $this->namedtag->getFloat("Scale"));
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
    
    public function saveSmaccerNbt(): void{
        $visibility = 0;
        if($this->isNameTagVisible()){
            $visibility = 1;
            if($this->isNameTagAlwaysVisible()){
                $visibility = 2;
            }
        }
        $scale = $this->getDataPropertyManager()->getFloat(Entity::DATA_SCALE);
        $this->namedtag->setInt("NameVisibility", $visibility, true);
        $this->namedtag->setFloat("Scale", $scale, true);
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
