<?php
declare(strict_types=1);

namespace uhc\session;

use pocketmine\player\Player;

/**
 * Sessions are permanent unlike players in PlayerManager.
 */
class SessionManager{
	/** @var PlayerSession[] */
	private $activeSessions = [];

	public function addSession(PlayerSession $session): void
	{
		if (!isset($this->activeSessions[$session->getUniqueId()->toString()])) {
			$this->activeSessions[$session->getUniqueId()->toString()] = $session;
		}
	}

	public function removeSession(PlayerSession $session): void
	{
		if (isset($this->activeSessions[$session->getUniqueId()->toString()])) {
			unset($this->activeSessions[$session->getUniqueId()->toString()]);
		}
	}

	public function hasSession(Player $player): bool
	{
		return isset($this->activeSessions[$player->getUniqueId()->toString()]);
	}

	/**
	 * @return PlayerSession[]
	 */
	public function getSessions(): array
	{
		return $this->activeSessions;
	}

	public function getSession(Player $player): ?PlayerSession
	{
		return $this->hasSession($player) ? $this->activeSessions[$player->getUniqueId()->toString()] : null;
	}
}