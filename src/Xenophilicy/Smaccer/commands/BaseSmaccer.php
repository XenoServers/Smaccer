<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class BaseSmaccer
 * @package Xenophilicy\BaseSmaccer\commands
 */
class BaseSmaccer extends PluginCommand {
    
    /** @var  SubSmaccer[] */
    protected $subCommands;
    
    public function __construct(){
        parent::__construct("smaccer", Smaccer::getInstance());
        $this->setPermission("smaccer");
        $this->setDescription("Smaccer management command");
        $this->subCommands = [];
    }
    
    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     *
     * @return mixed
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args){
        if(count($args) === 0 || !array_key_exists($args[0], $this->subCommands)){
            $sender->sendMessage(TextFormat::RED . "Usage: /smaccer <spawn|edit|id|remove|cancel|list|help>");
            return false;
        }
        return $this->subCommands[array_shift($args)]->execute($sender, $commandLabel, $args);
    }
    
    /**
     * @param string $name
     * @param SubSmaccer $command
     * @param array $aliases
     */
    public function registerSubSmaccer(string $name, SubSmaccer $command, $aliases = []){
        $this->subCommands[$name] = $command;
        foreach($aliases as $alias){
            if(!isset($this->subCommands[$alias])){
                $this->registerSubSmaccer($alias, $command);
            }
        }
    }
}