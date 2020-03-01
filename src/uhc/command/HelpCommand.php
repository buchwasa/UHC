<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use uhc\Loader;

class HelpCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("Help", $plugin);
		$this->plugin = $plugin;
		$this->setAliases(["help"]);
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		/*if($sender instanceof Player){
			$form = new SimpleForm("Help");
			$form->addButton("test", function(Player $player, $data){
				Server::getInstance()->broadcastMessage("TEST");
			});
			$sender->sendForm($form);
		}*/
		//$this->plugin->getServer()->dispatchCommand($sender, "COMMANDHERE");
		return true;
	}
}