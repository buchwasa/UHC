<?php

declare(strict_types=1);

namespace uhc\event;

use pocketmine\event\Event;
use pocketmine\player\Player;

class PhaseChangeEvent extends Event
{
	/** @var int */
	public const WAITING = -1;
	/** @var int */
	public const COUNTDOWN = 0;
	/** @var int */
	public const GRACE = 1;
	/** @var int */
	public const PVP = 2;
	/** @var int */
	public const NORMAL = 3;

	/** @var Player */
	private Player $player;
	/** @var int */
	private int $oldPhase;
	/** @var int */
	private int $newPhase;

	public function __construct(Player $player, int $oldPhase, int $newPhase)
	{
		$this->player = $player;
		$this->oldPhase = $oldPhase;
		$this->newPhase = $newPhase;
	}

	public function getPlayer(): Player
	{
		return $this->player;
	}

	public function getOldPhase(): int
	{
		return $this->oldPhase;
	}

	public function getNewPhase(): int
	{
		return $this->newPhase;
	}
}
