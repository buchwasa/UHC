<?php

declare(strict_types=1);

namespace uhc\command;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Toggle;
use pocketmine\player\Player;
use uhc\Loader;

class ScenariosCommand extends BaseCommand
{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin)
	{
		parent::__construct("scenario", "Shows the game's scenarios or sets them", "/scenario", ["sc"]);
		$this->plugin = $plugin;
	}

	public function onExecute(Player $sender, array $args): void
	{
		$toggles = [];
		foreach ($this->plugin->getScenarioManager()->getScenarios() as $scenario) {
			$toggles[] = new Toggle($scenario->getName(), $scenario->getName(), $scenario->isActive());
		}

		$form = new CustomForm("Scenarios", $toggles, function (Player $player, CustomFormResponse $response): void
		{
			foreach ($this->plugin->getScenarioManager()->getScenarios() as $scenario) {
				if (!$player->hasPermission("uhc.command.scenarios")) {
					return;
				}
				$scenario->setActive($response->getBool($scenario->getName()));
			}
		});

		$sender->sendForm($form);

		return;
	}
}
