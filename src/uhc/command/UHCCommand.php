<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use uhc\form\SimpleForm;
use uhc\GameHeartbeat;
use uhc\Loader;
use uhc\utils\GameStatus;

class UHCCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("uhc", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.uhc");
		$this->setUsage("/uhc");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender) || !$sender instanceof Player){
			return;
		}

		if($this->plugin->getHeartbeat()->hasStarted()){
			$sender->sendMessage(TextFormat::RED . "UHC already started!");
		}else{
			$this->plugin->getHeartbeat()->setGameStatus(GameStatus::COUNTDOWN);
			$sender->sendMessage(TextFormat::GREEN . "The UHC has been started successfully!");
		}

		return;
	}
}
