<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use uhc\Loader;

class GlobalMuteCommand extends BaseCommand
{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin)
	{
		parent::__construct("globalmute", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.globalmute");
	}

	public function onExecute(Player $sender, array $args): void
	{
		if (!$this->plugin->isGlobalMuteEnabled()) {
			$this->plugin->setGlobalMute(true);
			$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been disabled by an admin!");
			Command::broadcastCommandMessage($sender, "Disabled chat", false);
		} else {
			$this->plugin->setGlobalMute(false);
			$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been enabled by an admin!");
			Command::broadcastCommandMessage($sender, "Enabled chat", false);
		}
	}
}