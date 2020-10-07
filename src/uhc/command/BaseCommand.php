<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use uhc\Loader;

class BaseCommand extends Command
{
	/** @var Loader */
	private Loader $plugin;

	public function __construct(Loader $plugin, string $name, string $description, string $usageMessage, array $aliases = [])
	{
		parent::__construct($name, $description, $usageMessage, $aliases);
		$this->plugin = $plugin;
	}

	/**
	 * @param CommandSender $sender
	 * @param string $commandLabel
	 * @param string[] $args
	 * @return void
	 */
	public function execute(CommandSender $sender, string $commandLabel, array $args): void
	{
		if (!$sender instanceof Player) {
			$sender->sendMessage("You must be a player to execute this command!");
			return;
		}

		if (!$this->testPermission($sender)) {
			return;
		}

		$this->onExecute($sender, $args);
	}

	/**
	 * @param Player $sender
	 * @param string[] $args
	 */
	public function onExecute(Player $sender, array $args): void
	{

	}

	public function getPlugin(): Loader
	{
		return $this->plugin;
	}
}
