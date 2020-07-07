<?php

/*
 *
 *  ____            _        _   __  __ _                  __  __ ____
 * |  _ \ ___   ___| | _____| |_|  \/  (_)_ __   ___      |  \/  |  _ \
 * | |_) / _ \ / __| |/ / _ \ __| |\/| | | '_ \ / _ \_____| |\/| | |_) |
 * |  __/ (_) | (__|   <  __/ |_| |  | | | | | |  __/_____| |  | |  __/
 * |_|   \___/ \___|_|\_\___|\__|_|  |_|_|_| |_|\___|     |_|  |_|_|
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author PocketMine Team
 * @link http://www.pocketmine.net/
 *
 *
*/

declare(strict_types=1);

namespace dktapps\SerializedNbtFixer;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\NamedTag;
use ReflectionObject;
use ReflectionProperty;

/**
 * Class SerializedNbtFixer
 * @package dktapps\SerializedNbtFixer
 */
class SerializedNbtFixer {
    public static function fixSerializedCompoundTag(CompoundTag $tag): CompoundTag{
        $result = new CompoundTag($tag->getName());
        $properties = (new ReflectionObject($tag))->getProperties(ReflectionProperty::IS_PUBLIC);
        
        foreach($properties as $property){
            $child = $property->getValue($tag);
            if($child instanceof NamedTag){
                $result->setTag(self::processSerializedNamedTag($child));
            }
        }
        
        return $result;
    }
    
    public static function processSerializedNamedTag(NamedTag $tag): NamedTag{
        if($tag instanceof CompoundTag){
            return self::fixSerializedCompoundTag($tag);
        }elseif($tag instanceof ListTag){
            return self::fixSerializedListTag($tag);
        }else{
            return $tag;
        }
    }
    
    public static function fixSerializedListTag(ListTag $tag): ListTag{
        $properties = (new ReflectionObject($tag))->getProperties(ReflectionProperty::IS_PUBLIC);
        
        $result = [];
        foreach($properties as $i => $property){
            $child = $property->getValue($tag);
            if($child instanceof NamedTag and is_numeric($property->getName())){
                $result[(int)$i] = self::processSerializedNamedTag($child);
            }
        }
        
        return new ListTag($tag->getName(), $result);
    }
}
