<?php

namespace uhc\scenario;

use pocketmine\Player;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use uhc\event\UHCStartEvent;
use uhc\Loader;

class DoubleHealth extends Scenario{
	/** @var array */
	private $players = [];

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "DoubleHealth");
	}

	public function handleJoin(PlayerJoinEvent $ev) : void{
		$player = $ev->getPlayer();
		if($this->isActive()){
			$player->setMaxHealth(40);
			if(isset($this->players[$player->getName()])){
				$player->setHealth($this->players[$player->getName()]);
				unset($this->players[$player->getName()]);
			}else{
				$player->setHealth(40);
			}
		}
	}
	
	public function handleLeave(PlayerQuitEvent $ev) : void{
		if($this->isActive()){
			$this->players[$ev->getPlayer()->getName()] = $ev->getPlayer()->getHealth();
		}
	}

	public function handleStart(UHCStartEvent $ev) : void{
		if($this->isActive()){
			foreach($ev->getPlayers() as $player){
				$player->setMaxHealth(40);
				$player->setHealth(40);
			}
		}
	}
}