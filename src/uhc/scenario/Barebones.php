<?php

namespace uhc\scenario;

use pocketmine\block\BlockIds;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use uhc\Loader;

class Barebones extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "Barebones");
	}

	public function handleCraft(CraftItemEvent $ev) : void{
		if($this->isActive()){
			$items = $ev->getOutputs();
			foreach($items as $item){
				if($item->getId() === ItemIds::GOLDEN_APPLE){
					$ev->getPlayer()->sendMessage(TextFormat::RED . "You cannot craft this item in barebones scenario!");
					$ev->setCancelled(true);
				}
			}
		}
	}

	public function handleDeath(PlayerDeathEvent $ev) : void{
		if($this->isActive()){
			$ev->setDrops([
					ItemFactory::get(ItemIds::DIAMOND, 0, 1),
					ItemFactory::get(ItemIds::GOLDEN_APPLE, 0, 1),
					ItemFactory::get(ItemIds::ARROW, 0, 32),
					ItemFactory::get(ItemIds::STRING, 0, 2)
				]);
		}
	}

	public function handleBreak(BlockBreakEvent $ev) : void{
		if($this->isActive()){
			$dropIron = [
				BlockIds::GOLD_ORE,
				BlockIds::IRON_ORE,
				BlockIds::LAPIS_ORE,
				BlockIds::EMERALD_ORE,
				BlockIds::DIAMOND_ORE,
				BlockIds::REDSTONE_ORE,
				BlockIds::LIT_REDSTONE_ORE
			];

			if(in_array($ev->getBlock()->getId(), $dropIron)){
				$ev->setDrops([ItemFactory::get(ItemIds::IRON_INGOT, 0, 1)]);
			}
		}
	}
}