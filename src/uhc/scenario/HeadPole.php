<?php

namespace uhc\scenario;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\inventory\ShapedRecipe;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\math\Vector3;
use pocketmine\tile\Skull;
use pocketmine\tile\Tile;
use pocketmine\utils\TextFormat;
use uhc\GoldenHead;
use uhc\Loader;

class HeadPole extends Scenario{

	public function __construct(Loader $plugin){
		parent::__construct($plugin, "HeadPole");

		ItemFactory::registerItem(new GoldenHead(), true);
		$recipe = new ShapedRecipe(["aaa", "aba", "aaa"], [
			"a" => ItemFactory::get(ItemIds::GOLD_INGOT, 0, 1),
			"b" => ItemFactory::get(ItemIds::SKULL, 0, 1)
		], [ItemFactory::get(ItemIds::GOLDEN_APPLE, 1, 1)->setCustomName(TextFormat::RESET . TextFormat::GOLD . "Golden Head")]);
		$plugin->getServer()->getCraftingManager()->registerRecipe($recipe);
	}

	public function handleDeath(PlayerDeathEvent $ev) : void{
		$player = $ev->getPlayer();
		if($this->isActive()){
			$level = $player->getLevel();
			$level->setBlock(new Vector3($player->x, $player->y, $player->z), BlockFactory::get(BlockIds::NETHER_BRICK_FENCE));
			$level->setBlock(new Vector3($player->x, $player->y + 1, $player->z), BlockFactory::get(BlockIds::SKULL_BLOCK, 3), true, true);

			Tile::createTile(Tile::SKULL, $level, Skull::createNBT(new Vector3($player->x, $player->y + 1, $player->z), null, ItemFactory::get(ItemIds::SKULL, 3)));
		}
	}
}