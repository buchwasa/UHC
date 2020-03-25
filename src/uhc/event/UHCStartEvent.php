<?php

declare(strict_types=1);

namespace uhc\event;

use pocketmine\event\Event;
use pocketmine\Player;

class UHCStartEvent extends Event{

	/** @var Player[] */
	private $players = [];

	public function __construct(array $players){
		$this->players = $players;
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers() : array{
		return $this->players;
	}
}