<?php

declare(strict_types=1);

namespace uhc;

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
	private $heartbeat;
	/** @var PlayerManager */
	private $playerManager;
	/** @var ScenarioManager */
	private $scenarioManager;
	/** @var SessionManager */
	private $sessionManager;
	/** @var TeamManager */
	private $teamManager;
	/** @var bool */
	private $globalMuteEnabled = false;

	public function onEnable(): void
	{
		$this->getScheduler()->scheduleRepeatingTask($this->heartbeat = new GameHeartbeat($this), 20);
		$this->playerManager = new PlayerManager();
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

	public function getPlayerManager(): PlayerManager
	{
		return $this->playerManager;
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
}
