<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer\commands;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use Xenophilicy\Smaccer\Smaccer;

/**
 * Class BaseSlapper
 * @package Xenophilicy\BaseSlapper\commands
 */
class BaseSlapper extends PluginCommand {
    
    /** @var  SubSlapper[] */
    protected $subCommands;
    
    public function __construct(){
        parent::__construct("slapper", Smaccer::getInstance());
        $this->setPermission("slapper");
        $this->setDescription("Slapper management command");
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
            $sender->sendMessage(TextFormat::RED . "Usage: /slapper <spawn|edit|id|remove|cancel|list|help>");
            return false;
        }
        return $this->subCommands[array_shift($args)]->execute($sender, $commandLabel, $args);
    }
    
    /**
     * @param string $name
     * @param SubSlapper $command
     * @param array $aliases
     */
    public function registerSubSlapper(string $name, SubSlapper $command, $aliases = []){
        $this->subCommands[$name] = $command;
        foreach($aliases as $alias){
            if(!isset($this->subCommands[$alias])){
                $this->registerSubSlapper($alias, $command);
            }
        }
    }
}