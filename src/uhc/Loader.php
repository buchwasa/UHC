<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use uhc\command\GlobalMuteCommand;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectatorCommand;
use uhc\command\TeamCommand;
use uhc\command\TpallCommand;
use uhc\command\UHCCommand;
use uhc\game\GameHeartbeat;
use uhc\game\Scenario;
use uhc\game\Team;
use function is_array;
use function is_dir;
use function mkdir;
use function scandir;
use function str_replace;
use function substr;

class Loader extends PluginBase
{
    /** @var GameHeartbeat */
    private $heartbeat;
    /** @var Player[] */
    private $gamePlayers = [];
    /** @var PlayerSession[] */
    private $sessions = [];
    /** @var bool */
    private $globalMuteEnabled = false;
    /** @var Scenario[] */
    private $scenarios = [];
    /** @var Team[] */
    private $teams = [];

    public function onEnable(): void
    {
        if (!is_dir($this->getDataFolder() . "scenarios")) {
            mkdir($this->getDataFolder() . "scenarios");
        }
        $this->heartbeat = new GameHeartbeat($this);
        $this->getScheduler()->scheduleRepeatingTask($this->heartbeat, 20);
        new EventListener($this);

        $this->getServer()->getCommandMap()->registerAll("uhc", [
            new UHCCommand($this),
            new ScenariosCommand($this),
            new SpectatorCommand($this),
            new HealCommand($this),
            new GlobalMuteCommand($this),
            new TeamCommand($this),
            new TpallCommand($this)
        ]);
        $this->loadScenarios();
    }

    public function loadScenarios(): void
    {
        $dir = scandir($this->getDataFolder() . "scenarios");
        if (is_array($dir)) {
            foreach ($dir as $file) {
                $fileLocation = $this->getDataFolder() . "scenarios/" . $file;
                if (substr($file, -4) === ".php") {
                    require($fileLocation);
                    $class = "\\" . str_replace(".php", "", $file);
                    if (($scenario = new $class($this)) instanceof Scenario) {
                        $this->addScenario($scenario);
                    }
                }
            }
        }
    }

    public function getHeartbeat(): GameHeartbeat
    {
        return $this->heartbeat;
    }

    public function setGlobalMute(bool $enabled): void
    {
        $this->globalMuteEnabled = $enabled;
    }

    public function isGlobalMuteEnabled(): bool
    {
        return $this->globalMuteEnabled;
    }

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
    public function getGamePlayers(): array
    {
        return $this->gamePlayers;
    }

    public function isInGame(Player $player): bool
    {
        return isset($this->gamePlayers[$player->getUniqueId()->toString()]);
    }

    public function addSession(PlayerSession $session): void
    {
        if (!isset($this->sessions[$session->getUniqueId()->toString()])) {
            $this->sessions[$session->getUniqueId()->toString()] = $session;
        }
    }

    public function removeSession(PlayerSession $session): void
    {
        if (isset($this->sessions[$session->getUniqueId()->toString()])) {
            unset($this->sessions[$session->getUniqueId()->toString()]);
        }
    }

    public function hasSession(Player $player): bool
    {
        return isset($this->sessions[$player->getUniqueId()->toString()]);
    }

    /**
     * @return PlayerSession[]
     */
    public function getSessions(): array
    {
        return $this->sessions;
    }

    public function getSession(Player $player): ?PlayerSession
    {
        return $this->hasSession($player) ? $this->sessions[$player->getUniqueId()->toString()] : null;
    }

    /**
     * @return Scenario[]
     */
    public function getScenarios(): array
    {
        return $this->scenarios;
    }

    public function getScenario(string $scenarioName): Scenario
    {
        return $this->scenarios[$scenarioName];
    }

    public function addScenario(Scenario $scenario): void
    {
        $this->scenarios[$scenario->getName()] = $scenario;
    }

    /**
     * @return Team[]
     */
    public function getTeams(): array
    {
        return $this->teams;
    }

    public function addTeam(string $teamName, Player $teamLeader): void
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

    public function removeTeam(string $teamName): void
    {
        unset($this->teams[$teamName]);
    }
}
