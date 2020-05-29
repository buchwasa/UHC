<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\utils\UUID;
use uhc\game\Team;

class PlayerSession{
	/** @var UUID */
	private $uuid;
	/** @var Player */
	private $player;
	/** @var int[] */
	private $eliminations = [];
	/** @var Team|null */
	private $team = null;

	public function __construct(Player $player){
		$this->player = $player;
		$this->uuid = $player->getUniqueId();
		$this->eliminations[$player->getName()] = 0;
	}

	public function getUniqueId() : UUID{
		return $this->uuid;
	}

	public function getPlayer() : Player{
		return $this->player;
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

	public function getTeam() : ?Team{
		return $this->team;
	}

	public function isInTeam() : bool{
		return $this->team !== null;
	}

	public function addToTeam(Team $team) : bool{
		if($team->addMember($this->getPlayer()->getName())){
			$this->team = $team;
			return true;
		}

		return false;
	}

	public function removeFromTeam() : bool{
		if($this->team->removeMember($this->getPlayer()->getName())){
			$this->team = null;
			return true;
		}

		return false;
	}

	public static function create(Player $player) : self{
		return new self($player);
	}
}
