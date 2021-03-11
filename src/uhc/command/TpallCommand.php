<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\player\Player;
use uhc\UHC;

class TpallCommand extends BaseCommand
{

	public function __construct(UHC $plugin)
	{
		parent::__construct($plugin, "tpall", "Teleports everyone to the sender", "/tpall");
		$this->setPermission("uhc.command.tpall");
	}

	public function onExecute(Player $sender, array $args): void
	{
		foreach ($this->getPlugin()->getServer()->getOnlinePlayers() as $p) {
			$p->teleport($sender->getPosition());
		}
		Command::broadcastCommandMessage($sender, "Teleported everyone");
	}
}
