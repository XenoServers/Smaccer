<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;

/**
 * Class SubSlapper
 * @package Xenophilicy\BaseSlapper\commands
 */
abstract class SubSlapper {
    
    /**
     * @param CommandSender $sender
     * @param $commandLabel
     * @param array $args
     * @return mixed
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args);
}