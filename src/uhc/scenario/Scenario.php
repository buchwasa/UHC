<?php
declare(strict_types=1);

namespace uhc\scenario;

use pocketmine\event\Listener;
use uhc\Loader;

class Scenario implements Listener{
	/** @var Loader */
	private $loader;
	/** @var string */
	private $name;
	/** @var bool */
	private $activeScenario = false;

	public function __construct(Loader $loader, string $name){
		$loader->getServer()->getPluginManager()->registerEvents($this, $loader);
		$this->loader = $loader;
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