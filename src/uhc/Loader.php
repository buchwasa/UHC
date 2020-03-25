<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectatorCommand;
use uhc\command\UHCCommand;
use function is_array;
use function is_dir;
use function mkdir;
use function scandir;
use function str_replace;
use function substr;

class Loader extends PluginBase{

	/** @var Player[] */
	private $gamePlayers = [];
	/** @var array */
	private $eliminations = [];
	/** @var bool */
	private $globalMuteEnabled = false;
	/** @var Scenario[] */
	private $scenarios = [];

	public function onEnable() : void{
		if(!is_dir($this->getDataFolder() . "scenarios")){
			mkdir($this->getDataFolder() . "scenarios");
		}
		$this->getScheduler()->scheduleRepeatingTask(new UHCTimer($this), 20);
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
	
	public function setGlobalMute(bool $enabled) : void{
		$this->globalMuteEnabled = $enabled;
	}
	
	public function isGlobalMuteEnabled() : bool{
		return $this->globalMuteEnabled;
	}
	
	public function addToGame(Player $player) : void{
		if(!isset($this->gamePlayers[$player->getName()])){
			$this->gamePlayers[$player->getName()] = $player;
		}
	}
	
	public function removeFromGame(Player $player) : void{
		if(isset($this->gamePlayers[$player->getName()])){
			unset($this->gamePlayers[$player->getName()]);
		}
	}
	
	//TODO: phpdoc
	public function getGamePlayers() : array{
		return $this->gamePlayers;
	}
	
	public function isInGame(Player $player) : bool{
		return isset($this->gamePlayers[$player->getName()]);
	}

	public function addElimination(Player $player) : void{
		if(isset($this->eliminations[$player->getName()])){
			$this->eliminations[$player->getName()] = $this->eliminations[$player->getName()] + 1;
		}else{
			$this->eliminations[$player->getName()] = 1;
		}
	}

	public function getEliminations(Player $player) : int{
		if(isset($this->eliminations[$player->getName()])){
			return $this->eliminations[$player->getName()];
		}else{
			return $this->eliminations[$player->getName()] = 0;
		}
	}

	//TODO: phpdoc
	public function getScenarios() : array{
		return $this->scenarios;
	}

	public function addScenario(Scenario $scenario) : void{
		$this->scenarios[$scenario->getName()] = $scenario;
	}
}
