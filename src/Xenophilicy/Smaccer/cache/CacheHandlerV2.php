<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\cache;

use Exception;
use Generator;
use pocketmine\nbt\BigEndianNBTStream;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class CacheHandlerV2
 * @package Xenophilicy\Smaccer\cache
 */
class CacheHandlerV2 implements CacheReader {
    
    public const DATA_DIR = "cache_v2";
    public const STATE_FILE = "slappers_restored_file";
    
    public function storeSlapperNbt(string $nametag, string $type, string $levelName, CompoundTag $nbt): void{
        $pos = $nbt->getListTag("Pos");
        $nbt->removeTag("SlapperData");
        assert($pos instanceof ListTag);
        $dir = $this->getDirectory();
        @mkdir($dir, 0777, true);
        $writer = new BigEndianNBTStream();
        $data = $writer->writeCompressed($nbt);
        try{
            $filename = "$type.$nametag.$levelName." . bin2hex(random_bytes(8)) . ".nbt";
            file_put_contents($dir . $filename, $data);
        }catch(Exception $e){
        }
    }
    
    public function getDirectory(): string{
        return Smaccer::getInstance()->getDataFolder() . "cache_v2" . DIRECTORY_SEPARATOR;
    }
    
    /**
     * @return bool
     */
    public function isValid(): bool{
        return is_dir(Smaccer::getInstance()->getDataFolder() . self::DATA_DIR);
    }
    
    /**
     * @return bool
     */
    public function needsRestore(): bool{
        $trigger_file = $this->getDirectory() . self::STATE_FILE;
        return !is_file($trigger_file);
    }
    
    public function setNeedsRestore(bool $flag): void{
        $trigger_file = $this->getDirectory() . self::STATE_FILE;
        if(!$flag){
            @touch($trigger_file);
        }else{
            @unlink($trigger_file);
        }
    }
    
    public function nuke(): void{
        rename($this->getDirectory(), dirname($this->getDirectory()) . DIRECTORY_SEPARATOR . "cache_v2_nuked");
    }
    
    /**
     * @return Generator|CacheObject[]
     */
    public function uncacheSlappers(): Generator{
        $files = glob($this->getDirectory() . "*.nbt");
        $reader = new BigEndianNBTStream();
        foreach($files as $file){
            $fileName = basename($file, ".nbt");
            Smaccer::getInstance()->getLogger()->debug(__FUNCTION__ . " Found Slapper in v2 format: $fileName");
            $data = file_get_contents($file);
            $nbt = $reader->readCompressed($data);
            assert($nbt instanceof CompoundTag);
            [$type, $name, $world,] = explode(".", $fileName);
            yield new CacheObject($name, $type, $world, $nbt);
        }
    }
}
