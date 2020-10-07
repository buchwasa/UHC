<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use uhc\event\PhaseChangeEvent;
use uhc\Loader;

class UHCCommand extends BaseCommand
{

	public function __construct(Loader $plugin)
	{
		parent::__construct($plugin, "uhc", "Starts the UHC", "/uhc");
		$this->setPermission("uhc.command.uhc");
	}

	public function onExecute(Player $sender, array $args): void
	{
		if ($this->getPlugin()->getHeartbeat()->hasStarted()) {
			$sender->sendMessage(TextFormat::RED . "UHC already started!");
		} else {
			$this->getPlugin()->getHeartbeat()->setPhase(PhaseChangeEvent::COUNTDOWN);
			$sender->sendMessage(TextFormat::GREEN . "The UHC has been started successfully!");
			Command::broadcastCommandMessage($sender, "Started the UHC", false);
		}
	}
}
