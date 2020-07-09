<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\cache;

use Generator;

/**
 * Interface CacheReader
 * @package SmaccerCache
 */
interface CacheReader {
    
    public function getDirectory(): string;
    
    public function needsRestore(): bool;
    
    public function setNeedsRestore(bool $flag): void;
    
    public function nuke(): void;
    
    /**
     * @return Generator|CacheObject[]
     */
    public function uncacheSmaccers(): Generator;
}
