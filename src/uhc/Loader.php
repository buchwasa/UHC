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
	public $queue = [];
	/** @var array */
	public $eliminations = [];
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
		
		if(is_array(scandir($this->getDataFolder() . "scenarios"))){
			foreach(scandir($this->getDataFolder() . "scenarios") as $files){
				if(substr($files, -4) === ".php"){
					require($this->getDataFolder() . "scenarios/" . $files);
					$scenario = str_replace(".php", "", $files);
					$class = "\\$scenario";
					$this->addScenario(new $class($this));
				}
			}
		}
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
