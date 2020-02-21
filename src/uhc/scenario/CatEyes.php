<?php
declare(strict_types=1);

namespace uhc\scenario;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use uhc\event\UHCStartEvent;
use uhc\Loader;

class CatEyes extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "Cat Eyes");
	}

	public function handleStart(UHCStartEvent $ev){
		if($this->isActive()){
			foreach($ev->getPlayers() as $player){
				$player->addEffect(new EffectInstance(Effect::getEffect(Effect::NIGHT_VISION), 2147483646, 1));
			}
		}
	}
}