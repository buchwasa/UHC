<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;
use uhc\Loader;

class GlobalMuteCommand extends BaseCommand
{

	public function __construct(Loader $plugin)
	{
		parent::__construct($plugin, "globalmute", "Controls whether all players can chat or not", "/globalmute");
		$this->setPermission("uhc.command.globalmute");
	}

	public function onExecute(Player $sender, array $args): void
	{
		if (!$this->getPlugin()->isGlobalMuteEnabled()) {
			$this->getPlugin()->setGlobalMute(true);
			$this->getPlugin()->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been disabled by an admin!");
			Command::broadcastCommandMessage($sender, "Disabled chat", false);
		} else {
			$this->getPlugin()->setGlobalMute(false);
			$this->getPlugin()->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been enabled by an admin!");
			Command::broadcastCommandMessage($sender, "Enabled chat", false);
		}
	}
}
