<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectatorCommand;
use uhc\command\UHCCommand;
use uhc\utils\Scenario;
use function is_array;
use function is_dir;
use function mkdir;
use function scandir;
use function str_replace;
use function substr;

class Loader extends PluginBase{

	/** @var GameHeartbeat */
	private $heartbeat;
	/** @var Player[] */
	private $gamePlayers = [];
	/** @var PlayerSession[] */
	private $sessions = [];
	/** @var array */
	private $eliminations = [];
	/** @var bool */
	private $globalMuteEnabled = false;
	/** @var Scenario[] */
	private $scenarios = [];

	public function onEnable(): void{
		if(!is_dir($this->getDataFolder() . "scenarios")){
			mkdir($this->getDataFolder() . "scenarios");
		}
		$this->heartbeat = new GameHeartbeat($this);
		$this->getScheduler()->scheduleRepeatingTask($this->heartbeat, 20);
		new EventListener($this);

		$this->getServer()->getCommandMap()->registerAll("uhc", [
			new UHCCommand($this),
			new ScenariosCommand($this),
			new SpectatorCommand($this),
			new HealCommand($this)
		]);

		$dir = scandir($this->getDataFolder() . "scenarios");
		if(is_array($dir)){
			foreach($dir as $file){
				if(substr($file, -4) === ".php"){
					require($this->getDataFolder() . "scenarios/" . $file);
					$class = "\\" . str_replace(".php", "", $file);
					if(($scenario = new $class($this)) instanceof Scenario){
						$this->addScenario($scenario);
					}
				}
			}
		}
	}

	/**
	 * @return GameHeartbeat
	 */
	public function getHeartbeat(): GameHeartbeat {
		return $this->heartbeat;
	}

	/**
	 * @param bool $enabled
	 */
	public function setGlobalMute(bool $enabled): void {
		$this->globalMuteEnabled = $enabled;
	}

	/**
	 * @return bool
	 */
	public function isGlobalMuteEnabled(): bool {
		return $this->globalMuteEnabled;
	}

	/**
	 * @param Player $player
	 */
	public function addToGame(Player $player): void {
		if(!isset($this->gamePlayers[$player->getName()])){
			$this->gamePlayers[$player->getName()] = $player;
		}
	}

	/**
	 * @param Player $player
	 */
	public function removeFromGame(Player $player): void {
		if(isset($this->gamePlayers[$player->getName()])){
			unset($this->gamePlayers[$player->getName()]);
		}
	}

	/**
	 * @return Player[]
	 */
	public function getGamePlayers(): array {
		return $this->gamePlayers;
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function isInGame(Player $player): bool {
		return isset($this->gamePlayers[$player->getName()]);
	}

	/**
	 * @param PlayerSession $session
	 */
	public function addSession(PlayerSession $session): void {
		if(!isset($this->sessions[$session->getUniqueId()->toString()])) {
			$this->sessions[$session->getUniqueId()->toString()] = $session;
		}
	}

	/**
	 * @param PlayerSession $session
	 */
	public function removeSession(PlayerSession $session): void {
		if(isset($this->sessions[$session->getUniqueId()->toString()])) {
			unset($this->sessions[$session->getUniqueId()->toString()]);
		}
	}

	/**
	 * @param Player $player
	 * @return bool
	 */
	public function hasSession(Player $player): bool {
		return isset($this->sessions[$player->getUniqueId()->toString()]);
	}

	/**
	 * @return PlayerSession[]
	 */
	public function getSessions(): array {
		return $this->sessions;
	}

	/**
	 * @param Player $player
	 * @return PlayerSession|null
	 */
	public function getSession(Player $player): ?PlayerSession {
		return $this->hasSession($player) ? $this->sessions[$player->getUniqueId()->toString()] : null;
	}

	/**
	 * @param Player $player
	 */
	public function addElimination(Player $player): void {
		if(isset($this->eliminations[$player->getName()])){
			$this->eliminations[$player->getName()] = $this->eliminations[$player->getName()] + 1;
		}else{
			$this->eliminations[$player->getName()] = 1;
		}
	}

	/**
	 * @param Player $player
	 * @return int
	 */
	public function getEliminations(Player $player): int{
		if(isset($this->eliminations[$player->getName()])){
			return $this->eliminations[$player->getName()];
		}else{
			return $this->eliminations[$player->getName()] = 0;
		}
	}

	/**
	 * @return Scenario[]
	 */
	public function getScenarios(): array{
		return $this->scenarios;
	}

	/**
	 * @param Scenario $scenario
	 */
	public function addScenario(Scenario $scenario): void{
		$this->scenarios[$scenario->getName()] = $scenario;
	}
}
