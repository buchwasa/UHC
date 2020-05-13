<?php

declare(strict_types=1);

namespace uhc\event;

use pocketmine\event\player\PlayerEvent;
use pocketmine\Player;

class UHCStartEvent extends PlayerEvent{

	public function __construct(Player $player){
		$this->player = $player;
	}
}