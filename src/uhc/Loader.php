<?php

namespace uhc;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\HealCommand;
use uhc\command\HelpCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectatorCommand;
use uhc\command\UHCCommand;
use uhc\scenario\AppleRates;
use uhc\scenario\Barebones;
use uhc\scenario\BloodDiamond;
use uhc\scenario\CatEyes;
use uhc\scenario\CutClean;
use uhc\scenario\DoubleHealth;
use uhc\scenario\DoubleOres;
use uhc\scenario\DoubleOrNothing;
use uhc\scenario\EnchantedDeath;
use uhc\scenario\Fireless;
use uhc\scenario\HeadPole;
use uhc\scenario\NoFall;
use uhc\scenario\Scenario;
use uhc\tasks\UHCTimer;

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
		$this->getScheduler()->scheduleRepeatingTask(new UHCTimer($this), 20);
		new EventListener($this);

		$this->registerScenarios();
		$this->registerCommands();
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

	private function registerScenarios() : void{
		$this->addScenario(new AppleRates($this));
		$this->addScenario(new Barebones($this));
		$this->addScenario(new BloodDiamond($this));
		$this->addScenario(new CatEyes($this));
		$this->addScenario(new CutClean($this));
		$this->addScenario(new DoubleHealth($this));
		$this->addScenario(new DoubleOres($this));
		$this->addScenario(new DoubleOrNothing($this));
		$this->addScenario(new EnchantedDeath($this));
		$this->addScenario(new Fireless($this));
		$this->addScenario(new HeadPole($this));
		$this->addScenario(new NoFall($this));
	}

	private function registerCommands() : void{
		$keepCommands = [
			"gamemode",
			"ban",
			"pardon",
			"kick",
			"msg",
			"give",
			"kill",
			"op",
			"deop",
			"time",
			"tp",
			"whitelist",
			"tell",
			"stop"
		];
		foreach($this->getServer()->getCommandMap()->getCommands() as $cmd){
			if(!in_array($cmd->getName(), $keepCommands)){
				$this->getServer()->getCommandMap()->unregister($cmd);
			}
		}

		$this->getServer()->getCommandMap()->registerAll("uhc", [
			new UHCCommand($this),
			new ScenariosCommand($this),
			new SpectatorCommand($this),
			new HelpCommand($this),
			new HealCommand($this)
		]);
	}
}