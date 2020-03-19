<?php

namespace uhc;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectatorCommand;
use uhc\command\UHCCommand;
use uhc\Scenario;

class Loader extends PluginBase{

	/** @var Player[] */
	private $queue = [];
	/** @var array */
	private $eliminations = [];
	/** @var bool */
	public $globalMute = false;
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
		
		$dir = scandir($this->getDataFolder() . "scenarios")
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
	
	public function addToQueue(Player $player) : void{
		if(!isset($this->queue[$player->getName()])){
			$this->queue[$player->getName()] = $player;
		}
	}
	
	public function removeFromQueue(Player $player) : void{
		if(isset($this->queue[$player->getName()])){
			unset($this->queue[$player->getName()]);
		}
	}
	
	//TODO: phpdoc
	public function getQueue() : array{
		return $this->queue;
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
