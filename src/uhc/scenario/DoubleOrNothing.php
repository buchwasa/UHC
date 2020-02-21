<?php

namespace uhc\scenario;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use uhc\Loader;

class DoubleOrNothing extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "Double Or Nothing");
	}

	public function handleBreak(BlockBreakEvent $ev){
		if($this->isActive()){
			switch($ev->getBlock()->getId()){
				case Block::IRON_ORE:
				case Block::GOLD_ORE:
				case Block::DIAMOND_ORE:
					switch(mt_rand(1, 2)){
						case 1:
							foreach($ev->getDrops() as $drop){
								$ev->setDrops([ItemFactory::get($drop->getId(), 0, 2)]);
							}
							break;
						case 2:
							$ev->setDrops([]);
							break;
					}
					break;
			}
		}
	}
}