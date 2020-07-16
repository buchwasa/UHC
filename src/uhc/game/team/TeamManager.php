<?php
declare(strict_types=1);

namespace uhc\game\team;

use pocketmine\player\Player;

class TeamManager{
	/** @var Team[] */
	private $teams = [];

	/**
	 * @return Team[]
	 */
	public function getTeams(): array
	{
		return $this->teams;
	}

	public function createTeam(string $teamName, Player $teamLeader): void
	{
		$this->teams[$teamName] = new Team($teamName, $teamLeader);
	}

	public function getTeam(string $teamName): ?Team
	{
		return $this->teamExists($teamName) ? $this->teams[$teamName] : null;
	}

	public function teamExists(string $teamName): bool
	{
		return isset($this->teams[$teamName]);
	}

	public function disbandTeam(string $teamName): void
	{
		unset($this->teams[$teamName]);
	}
}