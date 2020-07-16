<?php
declare(strict_types=1);

namespace uhc\game\player;

use pocketmine\player\Player;

/**
 * Handles the players that are present for the UHC, temporary, unlike PlayerSession.
 */
class PlayerManager{
	/** @var Player[] */
	private $gamePlayers = [];

	public function addToGame(Player $player): void
	{
		if (!isset($this->gamePlayers[$player->getUniqueId()->toString()])) {
			$this->gamePlayers[$player->getUniqueId()->toString()] = $player;
		}
	}

	public function removeFromGame(Player $player): void
	{
		if (isset($this->gamePlayers[$player->getUniqueId()->toString()])) {
			unset($this->gamePlayers[$player->getUniqueId()->toString()]);
		}
	}

	/**
	 * @return Player[]
	 */
	public function getPlayers(): array
	{
		return $this->gamePlayers;
	}

	public function isPlaying(Player $player): bool
	{
		return isset($this->gamePlayers[$player->getUniqueId()->toString()]);
	}
}