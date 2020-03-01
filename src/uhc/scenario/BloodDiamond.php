<?php

namespace uhc\scenario;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use uhc\Loader;

class BloodDiamond extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "BloodDiamond");
	}

	public function handleBreak(BlockBreakEvent $ev) : void{
		if($this->isActive()){
			$player = $ev->getPlayer();
			if($ev->getBlock()->getId() === BlockIds::DIAMOND_ORE){
				$player->setHealth($player->getHealth() - 1);
				$player->broadcastEntityEvent(ActorEventPacket::HURT_ANIMATION);
			}
		}
	}
}