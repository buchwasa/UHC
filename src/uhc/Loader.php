<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\ScenariosCommand;
use uhc\command\TeamCommand;
use uhc\command\UHCCommand;
use uhc\game\GameHeartbeat;
use uhc\game\scenario\ScenarioManager;
use uhc\game\team\TeamManager;
use uhc\session\SessionManager;

class Loader extends PluginBase
{
	/** @var GameHeartbeat */
	private GameHeartbeat $heartbeat;
	/** @var ScenarioManager */
	private ScenarioManager $scenarioManager;
	/** @var SessionManager */
	private SessionManager $sessionManager;
	/** @var TeamManager */
	private TeamManager $teamManager;

	public function onEnable(): void
	{
		$this->registerPermissions();
		$this->getScheduler()->scheduleRepeatingTask($this->heartbeat = new GameHeartbeat($this), 20);
		$this->scenarioManager = new ScenarioManager($this);
		$this->sessionManager = new SessionManager();
		$this->teamManager = new TeamManager();
		new EventListener($this);

		$this->getServer()->getCommandMap()->registerAll("uhc", [
			new UHCCommand($this),
			new ScenariosCommand($this),
			new TeamCommand($this)
		]);
	}

	public function getHeartbeat(): GameHeartbeat
	{
		return $this->heartbeat;
	}

	public function getScenarioManager(): ScenarioManager
	{
		return $this->scenarioManager;
	}

	public function getSessionManager(): SessionManager
	{
		return $this->sessionManager;
	}

	public function getTeamManager(): TeamManager
	{
		return $this->teamManager;
	}

	public function resetPlayer(Player $player, bool $fullReset = false): void
	{
		$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		$player->setHealth($player->getMaxHealth());
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$player->getEffects()->clear();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		if($fullReset){
			$player->teleport($player->getWorld()->getSafeSpawn());
			$player->setGamemode(GameMode::SURVIVAL());
		}
	}

	public function registerPermissions(): void
	{
		$parent = DefaultPermissions::registerPermission(new Permission("uhc", "Parent for all UHC permissions."));

		$commands = DefaultPermissions::registerPermission(new Permission("uhc.command", "Parent permission for all UHC commands."), [$parent]);
		DefaultPermissions::registerPermission(new Permission("uhc.command.scenarios", ""), [$commands]);
		DefaultPermissions::registerPermission(new Permission("uhc.command.uhc", ""), [$commands]);
		$commands->recalculatePermissibles();

		$parent->recalculatePermissibles();
	}
}
