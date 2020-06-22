<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use uhc\Loader;

class BaseCommand extends PluginCommand{

    public function __construct(string $name, Loader $plugin)
    {
        parent::__construct($name, $plugin);
    }

    /**
     * @param CommandSender $sender
     * @param string $commandLabel
     * @param string[] $args
     * @return void
     */
    public function execute(CommandSender $sender, string $commandLabel, array $args) : void
    {
        if(!$sender instanceof Player){
            $sender->sendMessage("You must be a player to execute this command!");
            return;
        }

        if(!$this->testPermission($sender)){
            return;
        }

        $this->onExecute($sender, $args);
    }

    /**
     * @param Player $sender
     * @param string[] $args
     */
    public function onExecute(Player $sender, array $args): void
    {

    }
}