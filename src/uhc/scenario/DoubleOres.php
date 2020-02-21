<?php

namespace uhc\scenario;

use pocketmine\block\Block;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\item\Item;
use uhc\Loader;

class DoubleOres extends Scenario{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "DoubleOres");
		$this->plugin = $plugin;
	}

	public function handleBreak(BlockBreakEvent $ev){
		if($this->isActive()){
			switch($ev->getBlock()->getId()){
				case Block::IRON_ORE:
				case Block::GOLD_ORE:
				case Block::DIAMOND_ORE:
					foreach($ev->getDrops() as $item){
						$ev->setDrops([Item::get($item->getId(), 0, 2)]);
					}
					break;
			}
		}
	}
}