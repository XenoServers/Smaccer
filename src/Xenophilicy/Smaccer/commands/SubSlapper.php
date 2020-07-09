<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;

/**
 * Class SubSmaccer
 * @package Xenophilicy\BaseSmaccer\commands
 */
abstract class SubSmaccer {
    
    /**
     * @param CommandSender $sender
     * @param $commandLabel
     * @param array $args
     * @return mixed
     */
    abstract public function execute(CommandSender $sender, string $commandLabel, array $args);
}