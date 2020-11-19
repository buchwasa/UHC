<?php
declare(strict_types=1);

namespace uhc\game\team;

use pocketmine\player\Player;

class TeamManager{
	/** @var Team[] */
	private array $teams = [];
	/** @var int */
	private int $teamNumbers = 1;

	/**
	 * @return Team[]
	 */
	public function getTeams(): array
	{
		return $this->teams;
	}

	public function createTeam(Player $teamLeader): Team
	{
		$team = new Team($this->teamNumbers, $teamLeader);
		$this->teams[$this->teamNumbers] = $team;
		$this->teamNumbers++;

		return $team;
	}

	public function getTeam(int $teamNumber): ?Team
	{
		return $this->teamExists($teamNumber) ? $this->teams[$teamNumber] : null;
	}

	public function teamExists(int $teamNumber): bool
	{
		return isset($this->teams[$teamNumber]);
	}

	public function disbandTeam(int $teamNumber): void
	{
		unset($this->teams[$teamNumber]);
	}
}
