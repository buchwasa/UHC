<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\utils\TextFormat as TF;
use uhc\Loader;

class HealCommand extends PluginCommand{

	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("heal", $plugin);
		$this->plugin = $plugin;
		$this->setUsage("/heal <playerName>");
		$this->setPermission("uhc.command.heal");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$this->testPermission($sender)){
			return true;
		}

		if(!isset($args[0])){
			throw new InvalidCommandSyntaxException();
		}

		$player = $this->plugin->getServer()->getPlayer(strtolower($args[0]));
		if($player !== null){
			$player->setHealth($player->getMaxHealth());
			$player->setFood($player->getMaxFood());
			$sender->sendMessage(TF::RED . "You have healed " . TF::BOLD . TF::AQUA . $player->getDisplayName() . TF::RESET . TF::RED . "!");
		}

		return false;
	}
}