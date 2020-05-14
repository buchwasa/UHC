<?php

declare(strict_types=1);

namespace uhc\command;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Toggle;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
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

	public function execute(CommandSender $sender, string $commandLabel, array $args){
		if(!$sender instanceof Player){
			$sender->sendMessage("Must be executed in-game!");

			return;
		}

		$toggles = [];
		foreach($this->plugin->getScenarios() as $scenario){
			$toggles[] = new Toggle($scenario->getName(), $scenario->getName(), $scenario->isActive());
		}

		$form = new CustomForm("Scenarios", $toggles, function(Player $player, CustomFormResponse $response) : void{
			foreach($this->plugin->getScenarios() as $scenario){
				if(!$this->testPermissionSilent($player)){
					return;
				}
				$scenario->setActive($response->getBool($scenario->getName()));
			}
		});

		$sender->sendForm($form);

		return;
	}
}