<?php
declare(strict_types=1);

namespace uhc\game\team;

use pocketmine\player\Player;

class TeamManager{
	/** @var Team[] */
	private array $teams = [];

	/**
	 * @return Team[]
	 */
	public function getTeams(): array
	{
		return $this->teams;
	}

	public function createTeam(Player $teamLeader): Team
	{
		$teamNumber = count($this->teams) + 1;
		$team = new Team($teamNumber, $teamLeader);
		$this->teams[$teamNumber] = $team;

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
