<?php

declare(strict_types=1);

namespace uhc\event;

use pocketmine\event\Event;
use pocketmine\Player;

class PhaseChangeEvent extends Event{
    /** @var Player */
    private $player;
    /** @var int */
    private $oldPhase;
    /** @var int */
    private $newPhase;

	public function __construct(Player $player, int $oldPhase, int $newPhase){
		$this->player = $player;
		$this->oldPhase = $oldPhase;
		$this->newPhase = $newPhase;
	}

	public function getPlayer() : Player{
	    return $this->player;
    }

    public function getOldPhase() : int{
        return $this->oldPhase;
    }

    public function getNewPhase() : int{
        return $this->newPhase;
    }
}