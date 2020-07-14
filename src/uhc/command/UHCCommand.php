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
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin)
	{
		parent::__construct("uhc", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.uhc");
		$this->setUsage("/uhc");
	}

	public function onExecute(Player $sender, array $args): void
	{
		if ($this->plugin->getHeartbeat()->hasStarted()) {
			$sender->sendMessage(TextFormat::RED . "UHC already started!");

			return;
		} else {
			$this->plugin->getHeartbeat()->setPhase(PhaseChangeEvent::COUNTDOWN);
			$sender->sendMessage(TextFormat::GREEN . "The UHC has been started successfully!");
			Command::broadcastCommandMessage($sender, "Started the UHC", false);
		}

		return;
	}
}
