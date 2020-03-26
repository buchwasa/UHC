<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\utils\UUID;
use uhc\utils\Scoreboard;

class PlayerSession{
	/** @var UUID */
	private $uuid;
	/** @var Player */
	private $player;
	/** @var Scoreboard */
	private $scoreboard;

	public function __construct(Player $player){
		$this->player = $player;
		$this->uuid = $player->getUniqueId();
		$this->scoreboard = new Scoreboard($this);
	}

	public function getUniqueId() : UUID{
		return $this->uuid;
	}

	public function setUniqueId(UUID $uuid) : void{
		$this->uuid = $uuid;
	}

	public function getPlayer() : Player{
		return $this->player;
	}

	public function getScoreboard() : Scoreboard{
		return $this->scoreboard;
	}

	public function setScoreboard(Scoreboard $scoreboard) : void{
		$this->scoreboard = $scoreboard;
	}

	public function setPlayer(Player $player) : void{
		$this->player = $player;
	}

	public static function create(Player $player) : self{
		return new self($player);
	}
}
