<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\cache;

use __PHP_Incomplete_Class;
use dktapps\SerializedNbtFixer\SerializedNbtFixer;
use Generator;
use InvalidArgumentException;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class CacheHandlerV1
 * @package Xenophilicy\Smaccer\cache
 */
class CacheHandlerV1 implements CacheReader {
    
    /** @var string */
    private $SmaccerStateFile = "smaccers_restored_file";
    
    /**
     * @return bool
     */
    public function isValid(): bool{
        return is_dir($this->getDirectory());
    }
    
    public function getDirectory(): string{
        return $this->getDataFolder() . "cache" . DIRECTORY_SEPARATOR;
    }
    
    private function getDataFolder(): string{
        return Smaccer::getInstance()->getDataFolder();
    }
    
    /**
     * @return bool
     */
    public function needsRestore(): bool{
        $trigger_file = $this->getDirectory() . $this->SmaccerStateFile;
        return !is_file($trigger_file);
    }
    
    public function setNeedsRestore(bool $flag): void{
        $trigger_file = $this->getDirectory() . $this->SmaccerStateFile;
        if(!$flag){
            @touch($trigger_file);
        }else{
            @unlink($trigger_file);
        }
    }
    
    public function nuke(): void{
        rename($this->getDirectory(), dirname($this->getDirectory()) . DIRECTORY_SEPARATOR . "cache_v1_nuked");
    }
    
    /**
     * @return Generator|CacheObject[]
     */
    public function uncacheSmaccers(): Generator{
        $files = glob($this->getDirectory() . "*.slp");
        foreach($files as $file){
            $fileName = basename($file, ".slp");
            [$typeToUse, $world, $name,] = explode(".", $fileName);
            $nbt = $this->convertNbt($file);
            yield new CacheObject($name, $typeToUse, $world, $nbt);
        }
    }
    
    /**
     * @param $file
     * @return CompoundTag|null
     */
    private function convertNbt($file){
        if(!$data = file_get_contents($file)){
            return null;
        }
        $nbt = SerializedNbtFixer::fixSerializedCompoundTag(unserialize($data));
        if(file_exists($file . ".inv")){
            $data = file_get_contents($file . ".inv");
            $inventoryArray = unserialize($data);
            $smaccerTag = new CompoundTag("SmaccerData");
            $smaccerTag->setTag(new ListTag("Armor", [self::fixSerializedItem($inventoryArray[0])->nbtSerialize(0), self::fixSerializedItem($inventoryArray[1])->nbtSerialize(1), self::fixSerializedItem($inventoryArray[2])->nbtSerialize(2), self::fixSerializedItem($inventoryArray[3])->nbtSerialize(3)]));
            $smaccerTag->setByte("HeldItemIndex", $inventoryArray[4]);
            $smaccerTag->setTag(self::fixSerializedItem($inventoryArray[5])->nbtSerialize(-1, "HeldItem"));
            $nbt->setTag($smaccerTag);
        }
        return $nbt;
    }
    
    private static function fixSerializedItem(object $item): Item{
        if($item instanceof __PHP_Incomplete_Class){
            $stdclass = self::fix_object($item);
            
            return ItemFactory::get($stdclass->id, $stdclass->meta, $stdclass->count, $stdclass->tags);
        }elseif($item instanceof Item){
            return $item;
        }else{
            throw new InvalidArgumentException("unexpected object of type " . get_class($item));
        }
    }
    
    /**
     * Takes an __PHP_Incomplete_Class and casts it to a stdClass object.
     * All properties will be made public in this step.
     * @see https://stackoverflow.com/a/28353091
     * @since  1.1.0
     * @param object $object __PHP_Incomplete_Class
     * @return object
     */
    private static function fix_object($object){
        $fix_key = function($matches){
            return ":" . strlen($matches[1]) . ":\"" . $matches[1] . "\"";
        };
        $dump = serialize($object);
        $dump = preg_replace('/^O:\d+:"[^"]++"/', 'O:8:"stdClass"', $dump);
        $dump = preg_replace_callback('/:\d+:"\0.*?\0([^"]+)"/', $fix_key, $dump);
        return unserialize($dump);
    }
    
}
