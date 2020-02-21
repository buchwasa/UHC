<?php

namespace uhc\scenario;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use uhc\Loader;

class NoFall extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "NoFall");
	}

	public function handleDamage(EntityDamageEvent $ev){
		if($this->isActive()){
			if($ev->getEntity() instanceof Player){
				if($ev->getCause() === EntityDamageEvent::CAUSE_FALL){
					$ev->setCancelled();
				}
			}
		}
	}
}