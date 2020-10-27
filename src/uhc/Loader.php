<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\GlobalMuteCommand;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectateCommand;
use uhc\command\TeamCommand;
use uhc\command\TpallCommand;
use uhc\command\UHCCommand;
use uhc\game\GameHeartbeat;
use uhc\game\player\PlayerManager;
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
	/** @var bool */
	private bool $globalMuteEnabled = false;

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
			new SpectateCommand($this),
			new HealCommand($this),
			new GlobalMuteCommand($this),
			new TeamCommand($this),
			new TpallCommand($this)
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

	public function setGlobalMute(bool $enabled): void
	{
		$this->globalMuteEnabled = $enabled;
	}

	public function isGlobalMuteEnabled(): bool
	{
		return $this->globalMuteEnabled;
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

		$commands = DefaultPermissions::registerPermission(new Permission("uhc.command", "Parent permission for all UHC commands."), $parent);
		DefaultPermissions::registerPermission(new Permission("uhc.command.globalmute", "", Permission::DEFAULT_OP), $commands);
		DefaultPermissions::registerPermission(new Permission("uhc.command.heal", "", Permission::DEFAULT_OP), $commands);
		DefaultPermissions::registerPermission(new Permission("uhc.command.scenarios", "", Permission::DEFAULT_OP), $commands);
		DefaultPermissions::registerPermission(new Permission("uhc.command.tpall", "", Permission::DEFAULT_OP), $commands);
		DefaultPermissions::registerPermission(new Permission("uhc.command.uhc", "", Permission::DEFAULT_OP), $commands);
		$commands->recalculatePermissibles();

		$bypass = DefaultPermissions::registerPermission(new Permission("uhc.bypass", "Parent permission for all UHC bypasses", Permission::DEFAULT_OP), $parent);
		DefaultPermissions::registerPermission(new Permission("uhc.bypass.globalmute", "", Permission::DEFAULT_OP), $bypass);
		$bypass->recalculatePermissibles();

		$parent->recalculatePermissibles();
	}
}
