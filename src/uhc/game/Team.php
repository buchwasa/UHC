<?php
declare(strict_types=1);

namespace uhc\game;

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

	public function __construct(string $teamName, string $teamLeader, int $limit = 2){
		$this->teamName = $teamName;
		$this->teamLeader = $teamLeader;
		$this->limit = $limit;

		$this->members[$teamLeader] = $teamLeader;
	}

	/**
	 * @return string[]
	 */
	public function getMembers() : array{
		return $this->members;
	}

	public function memberExists(string $playerName) : bool{
		return isset($this->members[$playerName]);
	}

	public function addMember(string $playerName) : bool{
		if((count($this->members)) === $this->limit || $playerName === $this->teamLeader){ //leader is the +1
			return false;
		}

		$this->members[$playerName] = $playerName;

		return true;
	}

	public function removeMember(string $playerName) : bool{
		if(!isset($this->members[$playerName]) || $this->teamLeader === $playerName){
			return false;
		}

		unset($this->members[$playerName]);

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
