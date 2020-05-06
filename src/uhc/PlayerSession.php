<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\utils\UUID;
use uhc\scoreboard\Scoreboard;

class PlayerSession{
	/** @var UUID */
	private $uuid;
	/** @var Player */
	private $player;
	/** @var Scoreboard */
	private $scoreboard;
	/** @var array TODO: deal with this later */
	private $eliminations = [];

	public function __construct(Player $player){
		$this->player = $player;
		$this->uuid = $player->getUniqueId();
		$this->scoreboard = new Scoreboard($this);
		$this->eliminations[$player->getName()] = 0;
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

	public function addElimination() : void{
		$this->eliminations[$this->player->getName()] = $this->eliminations[$this->player->getName()] + 1;
	}

	public function getEliminations() : int{
		return $this->eliminations[$this->player->getName()];
	}

	public static function create(Player $player) : self{
		return new self($player);
	}
}
