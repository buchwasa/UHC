<?php

declare(strict_types=1);

namespace uhc;

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
use uhc\game\Team;
use uhc\scenario\ScenarioManager;
use function is_dir;
use function mkdir;

class Loader extends PluginBase
{
	/** @var GameHeartbeat */
	private $heartbeat;
	/** @var ScenarioManager */
	private $scenarioManager;
	/** @var Player[] */
	private $gamePlayers = [];
	/** @var PlayerSession[] */
	private $sessions = [];
	/** @var bool */
	private $globalMuteEnabled = false;
	/** @var Team[] */
	private $teams = [];

	public function onEnable(): void
	{
		if (!is_dir($this->getDataFolder() . "scenarios")) {
			mkdir($this->getDataFolder() . "scenarios");
		}
		$this->heartbeat = new GameHeartbeat($this);
		$this->getScheduler()->scheduleRepeatingTask($this->heartbeat, 20);
		$this->scenarioManager = new ScenarioManager($this);
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

	public function setGlobalMute(bool $enabled): void
	{
		$this->globalMuteEnabled = $enabled;
	}

	public function isGlobalMuteEnabled(): bool
	{
		return $this->globalMuteEnabled;
	}

	public function addToGame(Player $player): void
	{
		if (!isset($this->gamePlayers[$player->getUniqueId()->toString()])) {
			$this->gamePlayers[$player->getUniqueId()->toString()] = $player;
		}
	}

	public function removeFromGame(Player $player): void
	{
		if (isset($this->gamePlayers[$player->getUniqueId()->toString()])) {
			unset($this->gamePlayers[$player->getUniqueId()->toString()]);
		}
	}

	/**
	 * @return Player[]
	 */
	public function getGamePlayers(): array
	{
		return $this->gamePlayers;
	}

	public function isInGame(Player $player): bool
	{
		return isset($this->gamePlayers[$player->getUniqueId()->toString()]);
	}

	public function addSession(PlayerSession $session): void
	{
		if (!isset($this->sessions[$session->getUniqueId()->toString()])) {
			$this->sessions[$session->getUniqueId()->toString()] = $session;
		}
	}

	public function removeSession(PlayerSession $session): void
	{
		if (isset($this->sessions[$session->getUniqueId()->toString()])) {
			unset($this->sessions[$session->getUniqueId()->toString()]);
		}
	}

	public function hasSession(Player $player): bool
	{
		return isset($this->sessions[$player->getUniqueId()->toString()]);
	}

	/**
	 * @return PlayerSession[]
	 */
	public function getSessions(): array
	{
		return $this->sessions;
	}

	public function getSession(Player $player): ?PlayerSession
	{
		return $this->hasSession($player) ? $this->sessions[$player->getUniqueId()->toString()] : null;
	}

	/**
	 * @return Team[]
	 */
	public function getTeams(): array
	{
		return $this->teams;
	}

	public function addTeam(string $teamName, Player $teamLeader): void
	{
		$this->teams[$teamName] = new Team($teamName, $teamLeader);
	}

	public function getTeam(string $teamName): ?Team
	{
		return $this->teamExists($teamName) ? $this->teams[$teamName] : null;
	}

	public function teamExists(string $teamName): bool
	{
		return isset($this->teams[$teamName]);
	}

	public function removeTeam(string $teamName): void
	{
		unset($this->teams[$teamName]);
	}
}
