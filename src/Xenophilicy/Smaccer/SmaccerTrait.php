<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\DataPropertyManager;
use pocketmine\entity\Entity;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\network\mcpe\protocol\SetActorDataPacket as SetEntityDataPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use Xenophilicy\Smaccer\entities\SmaccerEntity;

/**
 * Trait containing methods used in various Smaccers.
 */
trait SmaccerTrait {
    /** @var CompoundTag */
    public $namedtag;
    
    public function update(): bool{
        if($this->namedtag->hasTag(SmaccerEntity::TAG_SPIN)){
            if(!in_array($this->getSaveId(), ["SmaccerFallingSand", "SmaccerMinecart", "SmaccerBoat", "SmaccerPrimedTNT", "SmaccerShulker"])){
                $this->setRotation($this->getYaw() + ($this->namedtag->getFloat(SmaccerEntity::TAG_SPIN) / 10), $this->getPitch());
            }
        }
        if($this->namedtag->hasTag(SmaccerEntity::TAG_SERVER)){
            $online = 0;
            $maximum = 0;
            $servers = $this->namedtag->getCompoundTag(SmaccerEntity::TAG_SERVER);
            foreach($servers as $server){
                $data = QueryManager::getResult($server->getValue());
                if(is_null($data)) continue;
                $online += $data[0];
                $maximum += $data[1];
            }
            if($maximum === 0) $online = $maximum = "-";
            $format = Smaccer::$settings["Default"]["count-tags"]["servers"];
            $nametag = str_replace(["{players}", "{maximum}"], [$online, $maximum], $format);
            $this->setNameTag($this->namedtag->getString(SmaccerEntity::TAG_NAME) . TF::EOL . $nametag);
            $this->sendData($this->getViewers());
            return true;
        }
        if($this->namedtag->hasTag(SmaccerEntity::TAG_WORLD)){
            $online = 0;
            $worlds = $this->namedtag->getCompoundTag(SmaccerEntity::TAG_WORLD);
            foreach($worlds as $world){
                $level = Smaccer::getInstance()->getServer()->getLevelByName($world->getValue());
                if(is_null($level)) continue;
                $online += sizeof($level->getPlayers());
            }
            $format = Smaccer::$settings["Default"]["count-tags"]["worlds"];
            $nametag = str_replace("{players}", $online, $format);
            $this->setNameTag($this->namedtag->getString(SmaccerEntity::TAG_NAME) . TF::EOL . $nametag);
            $this->sendData($this->getViewers());
            return true;
        }
        $this->setNameTag($this->namedtag->getString(SmaccerEntity::TAG_NAME, ""));
        $this->sendData($this->getViewers());
        return true;
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
    
    /**
     * @return DataPropertyManager
     */
    abstract public function getDataPropertyManager(): DataPropertyManager;
    
    abstract public function sendNameTag(Player $player): void;
    
    public function prepareMetadata(): void{
        $this->setGenericFlag(Entity::DATA_FLAG_IMMOBILE, true);
        if(!$this->namedtag->hasTag(SmaccerEntity::TAG_SCALE, FloatTag::class)){
            $this->namedtag->setFloat(SmaccerEntity::TAG_SCALE, 1.0, true);
        }
        $this->getDataPropertyManager()->setFloat(Entity::DATA_SCALE, $this->namedtag->getFloat(SmaccerEntity::TAG_SCALE));
    }
    
    abstract public function setGenericFlag(int $flag, bool $value = true): void;
    
    public function tryChangeMovement(): void{
    
    }
    
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
