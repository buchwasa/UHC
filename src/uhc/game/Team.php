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

	public function addMember(string $playerName) : void{
		if((count($this->members) + 1) === $this->limit || $playerName === $this->teamLeader){ //leader is the +1
			return;
		}

		$this->members[$playerName] = $playerName;
	}

	public function removeMember(string $playerName) : void{
		if(!isset($this->members[$playerName])){
			return;
		}

		unset($this->members[$playerName]);
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
