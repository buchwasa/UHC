<?php

declare(strict_types=1);

namespace uhc;


use pocketmine\Player;
use pocketmine\utils\UUID;
use uhc\utils\Scoreboard;

class PlayerSession {

	/** @var UUID */
	private $uuid;

	/** @var Player */
	private $player;

	/** @var Scoreboard */
	private $scoreboard;

	/**
	 * PlayerSession constructor.
	 * @param Player $player
	 */
	public function __construct(Player $player) {
		$this->setPlayer($player);
		$this->setUniqueId($player->getUniqueId());
		$this->setScoreboard(new Scoreboard($this));
	}

	/**
	 * @return UUID
	 */
	public function getUniqueId(): UUID {
		return $this->uuid;
	}

	/**
	 * @param UUID $uuid
	 */
	public function setUniqueId(UUID $uuid): void {
		$this->uuid = $uuid;
	}

	/**
	 * @return Player
	 */
	public function getPlayer(): Player {
		return $this->player;
	}

	/**
	 * @return Scoreboard
	 */
	public function getScoreboard(): Scoreboard {
		return $this->scoreboard;
	}

	/**
	 * @param Scoreboard $scoreboard
	 */
	public function setScoreboard(Scoreboard $scoreboard): void {
		$this->scoreboard = $scoreboard;
	}

	/**
	 * @param Player $player
	 */
	public function setPlayer(Player $player): void {
		$this->player = $player;
	}

	/**
	 * @param Player $player
	 * @return PlayerSession
	 */
	static public function create(Player $player): PlayerSession {
		return new PlayerSession($player);
	}

}