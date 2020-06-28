<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\player\Player;
use uhc\Loader;

class BaseCommand extends PluginCommand implements CommandExecutor{

    public function __construct(string $name, Loader $plugin)
    {
        parent::__construct($name, $plugin, $this);
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
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		return false;
	}
}