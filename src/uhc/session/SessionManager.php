<?php
declare(strict_types=1);

namespace uhc\session;

use pocketmine\player\Player;

/**
 * Sessions are permanent unlike players in PlayerManager.
 */
class SessionManager{
	/** @var PlayerSession[] */
	private array $activeSessions = [];

	/**
	 * Creates a new session for a given player.
	 *
	 * @param Player $player
	 */
	public function createSession(Player $player): void
	{
		if (!$this->hasSession($player)) {
			$this->activeSessions[$player->getUniqueId()->toString()] = new PlayerSession($player);
		}
	}

	/**
	 * Removes a session from a given player.
	 *
	 * @param Player $player
	 */
	public function removeSession(Player $player): void
	{
		if ($this->hasSession($player)) {
			unset($this->activeSessions[$player->getUniqueId()->toString()]);
		}
	}

	/**
	 * Checks if a session exists for a given player, returns false if not.
	 *
	 * @param Player $player
	 * @return bool
	 */
	public function hasSession(Player $player): bool
	{
		return isset($this->activeSessions[$player->getUniqueId()->toString()]);
	}

	/**
	 * Returns an array of player sessions.
	 *
	 * @return PlayerSession[]
	 */
	public function getSessions(): array
	{
		return $this->activeSessions;
	}

	/**
	 * Gets a given player's session, returns null if there is no session.
	 *
	 * @param Player $player
	 * @return PlayerSession|null
	 */
	public function getSession(Player $player): ?PlayerSession
	{
		return $this->hasSession($player) ? $this->activeSessions[$player->getUniqueId()->toString()] : null;
	}

	/**
	 * @return PlayerSession[]
	 */
	public function getPlaying(): array
	{
		$playing = [];
		foreach ($this->getSessions() as $session) {
			if ($session->isPlaying() && $session->getPlayer()->isOnline()) {
				$playing[] = $session;
			}
		}

		return $playing;
	}
}
