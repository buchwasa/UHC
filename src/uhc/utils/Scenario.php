<?php

declare(strict_types=1);

namespace uhc\utils;

use pocketmine\event\Event;
use pocketmine\event\Listener;
use uhc\Loader;

class Scenario implements Listener{
	/** @var string */
	private $name;
	/** @var bool */
	private $activeScenario = false;

	public function __construct(Loader $plugin, string $name){
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
		$this->name = $name;
	}

	public function getName() : string{
		return $this->name;
	}

	public function setActive(bool $active) : void{
		$this->activeScenario = $active;
	}

	public function isActive() : bool{
		return $this->activeScenario;
	}
}
