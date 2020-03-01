<?php

namespace uhc\scenario;

use pocketmine\event\inventory\CraftItemEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\utils\TextFormat;
use uhc\Loader;

class EnchantedDeath extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "EnchantedDeath");
	}

	public function onCraft(CraftItemEvent $ev) : void{
		if($this->isActive()){
			$items = $ev->getOutputs();
			foreach($items as $item){
				if($item->getId() === ItemIds::ENCHANTING_TABLE){
					$ev->getPlayer()->sendMessage(TextFormat::RED . "You cannot craft this item in enchanted death scenario!");
					$ev->setCancelled(true);
				}
			}
		}
	}

	public function onDeath(PlayerDeathEvent $ev) : void{
		if($this->isActive()){
			$ev->getPlayer()->getLevel()->dropItem($ev->getPlayer()->getPosition(), ItemFactory::get(ItemIds::ENCHANTING_TABLE, 0, 1));
		}
	}
}