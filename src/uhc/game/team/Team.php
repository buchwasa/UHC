<?php
declare(strict_types=1);

namespace uhc\game\team;

use pocketmine\player\Player;
use function count;

class Team
{
	/** @var int */
	private int $teamNumber;
	/** @var Player */
	private Player $teamLeader;
	/** @var Player[] */
	private array $members = [];

	public function __construct(int $teamNumber, Player $teamLeader)
	{
		$this->teamNumber = $teamNumber;
		$this->teamLeader = $teamLeader;

		$this->members[$teamLeader->getUniqueId()->toString()] = $teamLeader;
	}

	/**
	 * @return Player[]
	 */
	public function getMembers(): array
	{
		return $this->members;
	}

	public function memberExists(Player $player): bool
	{
		return isset($this->members[$player->getUniqueId()->toString()]);
	}

	public function addMember(Player $player): bool
	{
		if ($this->isLeader($player) || $this->isFull()) {
			return false;
		}

		$this->members[$player->getUniqueId()->toString()] = $player;

		return true;
	}

	public function removeMember(Player $player): bool
	{
		if ($this->isLeader($player) || !isset($this->members[$player->getUniqueId()->toString()])) {
			return false;
		}

		unset($this->members[$player->getUniqueId()->toString()]);

		return true;
	}

	public function getNumber(): int
	{
		return $this->teamNumber;
	}

	public function getLeader(): Player
	{
		return $this->teamLeader;
	}

	public function isLeader(Player $player): bool
	{
		return $this->teamLeader->getUniqueId()->toString() === $player->getUniqueId()->toString();
	}

	public function isFull(): bool
	{
		return count($this->members) === 2; //TODO: Remove hardcoded max team size
	}
}
