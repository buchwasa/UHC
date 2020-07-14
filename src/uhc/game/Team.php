<?php
declare(strict_types=1);

namespace uhc\game;

use pocketmine\player\Player;
use function count;

class Team
{
    /** @var string */
    private $teamName;
    /** @var Player */
    private $teamLeader;
    /** @var Player[] */
    private $members = [];
    /** @var int */
    public const TEAM_LIMIT = 2;

    public function __construct(string $teamName, Player $teamLeader)
    {
        $this->teamName = $teamName;
        $this->teamLeader = $teamLeader;

        $this->members[$teamLeader->getUniqueId()->toString()] = $teamLeader;
    }

    /**
     * @return Player[]
     */
    public function getMembers(): array
    {
        return $this->members;
    }

    public function memberExists(Player $player): bool
    {
        return isset($this->members[$player->getUniqueId()->toString()]);
    }

    public function addMember(Player $player): bool
    {
        if ($this->isFull() || $player->getUniqueId() === $this->teamLeader->getUniqueId()) {
            return false;
        }

        $this->members[$player->getUniqueId()->toString()] = $player;

        return true;
    }

    public function removeMember(Player $player): bool
    {
        if (!isset($this->members[$player->getUniqueId()->toString()]) || $player->getUniqueId() === $this->teamLeader->getUniqueId()) {
            return false;
        }

        unset($this->members[$player->getUniqueId()->toString()]);

        return true;
    }

    public function getName(): string
    {
        return $this->teamName;
    }

    public function getLeader(): Player
    {
        return $this->teamLeader;
    }

    public function isFull(): bool
    {
        return count($this->members) === self::TEAM_LIMIT;
    }
}
