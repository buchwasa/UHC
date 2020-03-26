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

/**
 * TODO: Change from PluginCommand, so that we can use getPlugin() w/ no issues
 */
class UHCCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("uhc", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.uhc");
		$this->setUsage("/uhc");
	}

	public function getLoader() : Loader{
		return $this->plugin;
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender) || !$sender instanceof Player){
			return true;
		}

		$form = new SimpleForm("UHC");
		$form->addButton("Start UHC", function(Player $player, $data){
			if($data === 0){
				if($this->getLoader()->getHeartbeat()->hasStarted()){
					$player->sendMessage(TextFormat::RED . "UHC already started!");
				}else{
					$this->getLoader()->getHeartbeat()->setGameStatus(GameStatus::COUNTDOWN);
					$player->sendMessage(TextFormat::GREEN . "The UHC has been started successfully!");
				}
			}
		});

		$form->addButton("Teleport All", function(Player $player, $data){
			if($data === 1){
				foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
					$p->teleport($player->getPosition());
				}
			}
		});

		$form->addButton("GlobalMute", function(Player $player, $data){
			if($data === 2){
				if(!$this->plugin->isGlobalMuteEnabled()){
					$this->plugin->setGlobalMute(true);
					$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been disabled by an admin!");
				}else{
					$this->plugin->setGlobalMute(false);
					$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been enabled by an admin!");
				}
			}
		});

		$sender->sendForm($form);

		return true;
	}
}
