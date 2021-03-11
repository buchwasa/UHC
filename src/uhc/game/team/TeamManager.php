<?php
declare(strict_types=1);

namespace uhc\game\team;

use pocketmine\player\Player;
use uhc\UHC;

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

	public function getTeamSize(): int{
		return UHC::getInstance()->config->get("team-size");
	}

	public function setTeamSize(int $size): void{
		UHC::getInstance()->config->set("team-size", $size);
	}
}
