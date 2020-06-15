<?php

declare(strict_types=1);

namespace uhc\game;

use JackMD\ScoreFactory\ScoreFactory;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use uhc\event\PhaseChangeEvent;
use uhc\game\type\GameStatus;
use uhc\game\type\GameTimer;
use uhc\Loader;
use wumpotamus\chunkloader\ChunkRegion;
use function floor;
use function mt_rand;

class GameHeartbeat extends Task
{
    /** @var int */
    private $gameStatus = GameStatus::WAITING;

    /** @var int */
    private $game = 0;
    /** @var int */
    private $countdown = GameTimer::TIMER_COUNTDOWN;
    /** @var float|int */
    private $grace = GameTimer::TIMER_GRACE;
    /** @var float|int */
    private $pvp = GameTimer::TIMER_PVP;
    /** @var float|int */
    private $normal = GameTimer::TIMER_NORMAL;
    /** @var Border */
    private $border;
    /** @var Loader */
    private $plugin;

    /** @var int */
    private $playerTimer = 1;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        $this->border = new Border($plugin->getServer()->getDefaultLevel());
    }

    public function getGameStatus(): int
    {
        return $this->gameStatus;
    }

    public function setGameStatus(int $gameStatus): void
    {
        $this->gameStatus = $gameStatus;
    }

    public function hasStarted(): bool
    {
        return $this->getGameStatus() >= GameStatus::GRACE;
    }

    public function onRun(int $currentTick): void
    {
        $this->handlePlayers();
        switch ($this->getGameStatus()) {
            case GameStatus::COUNTDOWN:
                $this->handleCountdown();
                break;
            case GameStatus::GRACE:
                $this->handleGrace();
                break;
            case GameStatus::PVP:
                $this->handlePvP();
                break;
            case GameStatus::NORMAL:
                $this->handleNormal();
                break;
        }
        if ($this->hasStarted()) $this->game++;
    }

    private function handlePlayers(): void
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            if ($p->isSurvival()) {
                $this->plugin->addToGame($p);
            } else {
                $this->plugin->removeFromGame($p);
            }
            $this->handleScoreboard($p);
        }

        foreach ($this->plugin->getGamePlayers() as $player) {
            if (!$this->border->isPlayerInsideOfBorder($player)) {
                $this->border->teleportPlayer($player);
                $player->addTitle("You have been teleported by border!");
            }
            switch ($this->getGameStatus()) {
                case GameStatus::COUNTDOWN:
                    $player->setFood($player->getMaxFood());
                    $player->setHealth($player->getMaxHealth());
                    if ($this->countdown === 29) {
                        $this->randomizeCoordinates($player, 750);
                        $player->setWhitelisted(true);
                        $player->removeAllEffects();
                        $player->getInventory()->clearAll();
                        $player->getArmorInventory()->clearAll();
                        $player->getCursorInventory()->clearAll();
                        $player->setImmobile(true);
                    } elseif ($this->countdown === 3) {
                        $player->setImmobile(false);
                    }
                    break;
                case GameStatus::GRACE:
                    if ($this->grace === 601) {
                        $player->setHealth($player->getMaxHealth());
                    }
                    break;
            }
        }
    }

    private function handleCountdown(): void
    {
        $server = $this->plugin->getServer();
        switch ($this->countdown) {
            case 30:
                $server->setConfigBool("white-list", true);
                $server->broadcastTitle("Server has been " . TF::AQUA . "whitelisted!");
                $server->broadcastTitle("The game will begin in " . TF::AQUA . "30 seconds.");
                break;
            case 29:
                $server->broadcastTitle("Global Mute has been " . TF::AQUA . "enabled!");
                $this->plugin->setGlobalMute(true);
                break;
            case 10:
                $server->broadcastTitle("The game will begin in " . TF::AQUA . "10 seconds.");
                break;
            case 5:
            case 4:
            case 3:
            case 2:
            case 1:
                $server->broadcastTitle("The game will begin in " . TF::AQUA . "$this->countdown second(s).");
                break;
            case 0:
                foreach ($this->plugin->getServer()->getDefaultLevel()->getEntities() as $entity) {
                    if (!$entity instanceof Player) {
                        $entity->flagForDespawn();
                    }
                }

                foreach ($this->plugin->getGamePlayers() as $playerSession) {
                    $ev = new PhaseChangeEvent($playerSession, GameStatus::COUNTDOWN, GameStatus::GRACE);
                    $ev->call();
                }
                $server->broadcastTitle(TF::RED . TF::BOLD . "The UHC has begun!");
                $this->setGameStatus(GameStatus::GRACE);
                break;
        }
        $this->countdown--;
    }

    private function handleGrace(): void
    {
        $server = $this->plugin->getServer();
        switch ($this->grace) {
            case 1190:
                $server->broadcastTitle("Global Mute has been " . TF::AQUA . "disabled!");
                $this->plugin->setGlobalMute(false);
                $server->broadcastTitle("Final heal will occur in " . TF::AQUA . "10 minutes.");
                break;
            case 601:
                $server->broadcastTitle("Final heal has " . TF::AQUA . "occurred!");
                break;
            case 600:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in 10 minutes.");
                break;
            case 300:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in 5 minutes.");
                break;
            case 60:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in 1 minute.");
                break;
            case 30:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in 30 seconds.");
                break;
            case 10:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in 10 seconds.");
                break;
            case 5:
            case 4:
            case 3:
            case 2:
            case 1:
                $server->broadcastTitle(TF::RED . "PvP will be enabled in $this->grace second(s).");
                break;
            case 0:
                foreach ($this->plugin->getGamePlayers() as $playerSession) {
                    $ev = new PhaseChangeEvent($playerSession, GameStatus::GRACE, GameStatus::PVP);
                    $ev->call();
                }
                $server->broadcastTitle(TF::RED . "PvP has been enabled, good luck!");
                $this->setGameStatus(GameStatus::PVP);
                break;
        }
        $this->grace--;
    }

    private function handlePvP(): void
    {
        $server = $this->plugin->getServer();
        switch ($this->pvp) {
            case 900:
                $server->broadcastTitle("The border will shrink to " . TF::AQUA . "750" . TF::WHITE . " in " . TF::AQUA . "5 minutes");
                break;
            case 600:
                $this->border->setSize(750);
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "500" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
                break;
            case 300:
                $this->border->setSize(500);
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "250" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
                break;
            case 0:
                foreach ($this->plugin->getGamePlayers() as $playerSession) {
                    $ev = new PhaseChangeEvent($playerSession, GameStatus::PVP, GameStatus::NORMAL);
                    $ev->call();
                }
                $this->border->setSize(250);
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "100" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
                $this->setGameStatus(GameStatus::NORMAL);
                break;
        }
        $this->pvp--;
    }

    public function handleNormal(): void
    {
        $server = $this->plugin->getServer();
        switch ($this->normal) {
            case 3300:
                $this->border->setSize(100);
                $this->border->build();
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "25" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
                break;
            case 3000:
                $this->border->setSize(25);
                $this->border->build();
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "10" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
                break;
            case 2700:
                $this->border->setSize(10);
                $this->border->build();
                $server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".");
                break;
        }
        $this->normal--;
    }

    private function handleScoreboard(Player $p): void
    {
        ScoreFactory::setScore($p, "§ky§r §b" . $p->getDisplayName() . " §f§ky§r");
        if ($this->hasStarted()) {
            ScoreFactory::setScoreLine($p, 1, "§7---------------------");
            ScoreFactory::setScoreLine($p, 2, " §bGame Time: §f" . gmdate("H:i:s", $this->game));
            ScoreFactory::setScoreLine($p, 3, " §bRemaining: §f" . count($this->plugin->getGamePlayers()));
            ScoreFactory::setScoreLine($p, 4, " §bEliminations: §f" . $this->plugin->getSession($p)->getEliminations());
            ScoreFactory::setScoreLine($p, 5, " §bBorder: §f" . $this->border->getSize());
            ScoreFactory::setScoreLine($p, 6, " §bCenter: §f(" . $p->getLevel()->getSafeSpawn()->getFloorX() . ", " . $p->getLevel()->getSafeSpawn()->getFloorZ() . ")");
            ScoreFactory::setScoreLine($p, 7, "§7--------------------- ");
        } else {
            ScoreFactory::setScoreLine($p, 1, "§7---------------------");
            ScoreFactory::setScoreLine($p, 2, " §bPlayers: §f" . count($this->plugin->getGamePlayers()));
            ScoreFactory::setScoreLine($p, 3, $this->getGameStatus() === GameStatus::WAITING ? "§b Waiting for players..." : "§b Starting in:§f $this->countdown");
            ScoreFactory::setScoreLine($p, 4, "§7--------------------- ");
        }
    }

    private function randomizeCoordinates(Player $p, int $range): void
    {
        $this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function (int $currentTick) use ($p, $range) : void {
            $ss = $p->getLevel()->getSafeSpawn();
            $x = mt_rand($ss->getFloorX() - $range, $ss->getFloorX() + $range);
            $z = mt_rand($ss->getFloorZ() - $range, $ss->getFloorZ() + $range);

            ChunkRegion::onChunkGenerated($p->getLevel(), $x >> 4, $z >> 4, function () use ($p, $x, $z) {
                $p->teleport(new Vector3($x, $p->getLevel()->getHighestBlockAt($x, $z) + 1, $z));
            });

            $this->playerTimer += 5;
        }), $this->playerTimer);
    }
}
