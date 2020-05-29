<?php
declare(strict_types=1);

namespace uhc\game;

use pocketmine\Player;
use function count;

class Team{
	/** @var string */
	private $teamName;
	/** @var string */
	private $teamLeader;
	/** @var string[] */
	private $members = [];
	/** @var int */
	private $limit;

	public function __construct(string $teamName, Player $teamLeader, int $limit = 2){
		$this->teamName = $teamName;
		$this->teamLeader = $teamLeader;
		$this->limit = $limit;

		$this->members[$teamLeader->getUniqueId()] = $teamLeader;
	}

	/**
	 * @return string[]
	 */
	public function getMembers() : array{
		return $this->members;
	}

	public function memberExists(Player $player) : bool{
		return isset($this->members[$player->getUniqueId()]);
	}

	public function addMember(Player $player) : bool{
		if((count($this->members)) === $this->limit || $player->getName() === $this->teamLeader){ //leader is the +1
			return false;
		}

		$this->members[$player->getUniqueId()] = $player;

		return true;
	}

	public function removeMember(Player $player) : bool{
		if(!isset($this->members[$player->getUniqueId()]) || $player->getName() === $this->teamLeader){
			return false;
		}

		unset($this->members[$player->getUniqueId()]);

		return true;
	}

	public function getName() : string{
		return $this->teamName;
	}

	public function getLeader() : string{
		return $this->teamLeader;
	}

	public function getLimit() : int{
		return $this->limit;
	}
}
