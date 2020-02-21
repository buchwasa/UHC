<?php

namespace uhc\scenario;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\Player;
use uhc\Loader;

class Fireless extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "Fireless");
	}

	public function handleDamage(EntityDamageEvent $ev){
		if($this->isActive()){
			$entity = $ev->getEntity();
			$cause = $ev->getCause();
			if($entity instanceof Player){
				if($cause === EntityDamageEvent::CAUSE_FIRE || $cause === EntityDamageEvent::CAUSE_FIRE_TICK || $cause === EntityDamageEvent::CAUSE_LAVA){
					$ev->setCancelled();
					$entity->extinguish();
				}
			}
		}
	}
}