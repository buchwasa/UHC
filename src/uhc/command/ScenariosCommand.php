<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use uhc\form\CustomForm;
use uhc\Loader;

class ScenariosCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("scenario", $plugin);
		$this->plugin = $plugin;
		$this->setAliases(["sc"]);
		$this->setPermission("uhc.command.scenario");
		$this->setUsage("/scenario");
	}

	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$sender instanceof Player){
			$sender->sendMessage("Must be executed in-game!");

			return true;
		}

		$form = new CustomForm("Scenarios");
		foreach($this->plugin->getScenarios() as $scenario){
			$form->addToggle($scenario->getName(), $scenario->isActive(), function(Player $player, bool $data) use ($scenario){
				if(!$this->testPermissionSilent($player)){
					return;
				}
				$scenario->setActive($data);
			});
		}

		$sender->sendForm($form);

		return true;
	}
}