<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\plugin\Plugin;
use uhc\Loader;

class TpallCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("tpall", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.tpall");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			return;
		}

		if(!$this->testPermission($sender)){
			return;
		}

		foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
			$p->teleport($sender->getPosition());
		}
	}
}