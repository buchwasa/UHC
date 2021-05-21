<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\player\Player;
use uhc\Loader;
use xenialdan\customui\windows\CustomForm;

class ScenariosCommand extends BaseCommand
{

	public function __construct(Loader $plugin)
	{
		parent::__construct($plugin, "scenario", "Shows the game's scenarios or sets them", "/scenario", ["sc"]);
	}

	public function onExecute(Player $sender, array $args): void
	{
		$form = new CustomForm("Scenarios");
		foreach ($this->getPlugin()->getScenarioManager()->getScenarios() as $scenario) {
			$form->addToggle($scenario->getName(), $scenario->isActive());
		}

		$form->setCallable(function (Player $player, $data): void{
			$index = 0;
			foreach ($this->getPlugin()->getScenarioManager()->getScenarios() as $scenario) {
				$scenario->setActive($data[$index]);
				$index++;
			}
		});

		$sender->sendForm($form);
	}
}
