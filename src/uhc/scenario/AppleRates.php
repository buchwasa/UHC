<?php
declare(strict_types=1);

namespace uhc\scenario;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use uhc\Loader;

class AppleRates extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "AppleRates");
	}

	public function handleBreak(BlockBreakEvent $ev) : void{
		if($ev->getBlock()->getId() === BlockIds::LEAVES || $ev->getBlock()->getId() === BlockIds::LEAVES2){
			if($ev->getPlayer()->getInventory()->getItemInHand()->getId() === ItemIds::SHEARS){
				if(mt_rand(1, 5) === 1) $ev->setDrops([ItemFactory::get(ItemIds::APPLE)]);
			}else{
				if(mt_rand(1, 15) === 3) $ev->setDrops([ItemFactory::get(ItemIds::APPLE)]);
			}
		}
	}
}