<?php

declare(strict_types=1);

namespace uhc\game;

use jackmd\scorefactory\ScoreFactory;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use uhc\event\PhaseChangeEvent;
use uhc\game\type\GameTimer;
use uhc\Loader;
use muqsit\chunkloader\ChunkRegion;
use function mt_rand;

class GameHeartbeat extends Task
{
	/** @var int */
	private $phase = PhaseChangeEvent::WAITING;

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
		$this->border = new Border($plugin->getServer()->getWorldManager()->getDefaultWorld());
	}

	public function getPhase(): int
	{
		return $this->phase;
	}

	public function setPhase(int $phase): void
	{
		$this->phase = $phase;
	}

	public function hasStarted(): bool
	{
		return $this->getPhase() >= PhaseChangeEvent::GRACE;
	}

	public function onRun(): void
	{
		$this->handlePlayers();
		switch ($this->getPhase()) {
			case PhaseChangeEvent::COUNTDOWN:
				$this->handleCountdown();
				break;
			case PhaseChangeEvent::GRACE:
				$this->handleGrace();
				break;
			case PhaseChangeEvent::PVP:
				$this->handlePvP();
				break;
			case PhaseChangeEvent::NORMAL:
				$this->handleNormal();
				break;
		}
		if ($this->hasStarted()) $this->game++;
	}

	private function handlePlayers(): void
	{
		foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
			if ($p->isSurvival()) { //TODO: This really shouldn't be done the whole game
				$this->plugin->addToGame($p);
			} else {
				$this->plugin->removeFromGame($p);
			}
			$this->handleScoreboard($p);
		}

		foreach ($this->plugin->getGamePlayers() as $player) {
			$session = $this->plugin->getSession($player);
			if ($session !== null) {
				$name = $session->getTeam() !== null ? $session->getTeam()->getName() : "NO TEAM";
				$player->setNameTag(TF::GOLD . "[$name] " . $player->getDisplayName());
			}
			if (!$this->border->isPlayerInsideOfBorder($player)) {
				$this->border->teleportPlayer($player);
				$player->sendTitle("You have been teleported by border!");
			}
			switch ($this->getPhase()) {
				case PhaseChangeEvent::COUNTDOWN:
					$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
					$player->setHealth($player->getMaxHealth());
					if ($this->countdown === 29) {
						$this->randomizeCoordinates($player, 750);
						$player->setWhitelisted(true);
						$player->getEffects()->clear();
						$player->getInventory()->clearAll();
						$player->getArmorInventory()->clearAll();
						$player->getCursorInventory()->clearAll();
						$player->setImmobile(true);
					} elseif ($this->countdown === 3) {
						$player->setImmobile(false);
					}
					break;
				case PhaseChangeEvent::GRACE:
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
				foreach ($server->getWorldManager()->getDefaultWorld()->getEntities() as $entity) {
					if (!$entity instanceof Player) {
						$entity->flagForDespawn();
					}
				}

				foreach ($this->plugin->getGamePlayers() as $playerSession) {
					$ev = new PhaseChangeEvent($playerSession, PhaseChangeEvent::COUNTDOWN, PhaseChangeEvent::GRACE);
					$ev->call();
				}
				$server->broadcastTitle(TF::RED . TF::BOLD . "The UHC has begun!");
				$this->setPhase(PhaseChangeEvent::GRACE);
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
					$ev = new PhaseChangeEvent($playerSession, PhaseChangeEvent::GRACE, PhaseChangeEvent::PVP);
					$ev->call();
				}
				$server->broadcastTitle(TF::RED . "PvP has been enabled, good luck!");
				$this->setPhase(PhaseChangeEvent::PVP);
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
					$ev = new PhaseChangeEvent($playerSession, PhaseChangeEvent::PVP, PhaseChangeEvent::NORMAL);
					$ev->call();
				}
				$this->border->setSize(250);
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border->getSize() . ".\nShrinking to " . TF::AQUA . "100" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				$this->setPhase(PhaseChangeEvent::NORMAL);
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
		$safeSpawn = $p->getWorld()->getSafeSpawn();
		ScoreFactory::setScore($p, "§ky§r §b" . $p->getDisplayName() . " §f§ky§r");
		if ($this->hasStarted()) {
			ScoreFactory::setScoreLine($p, 1, "§7---------------------");
			ScoreFactory::setScoreLine($p, 2, " §bGame Time: §f" . gmdate("H:i:s", $this->game));
			ScoreFactory::setScoreLine($p, 3, " §bRemaining: §f" . count($this->plugin->getGamePlayers()));
			ScoreFactory::setScoreLine($p, 4, " §bEliminations: §f" . $this->plugin->getSession($p)->getEliminations());
			ScoreFactory::setScoreLine($p, 5, " §bBorder: §f" . $this->border->getSize());
			ScoreFactory::setScoreLine($p, 6, " §bCenter: §f({$safeSpawn->getFloorX()}, {$safeSpawn->getFloorZ()})");
			ScoreFactory::setScoreLine($p, 7, "§7--------------------- ");
		} else {
			ScoreFactory::setScoreLine($p, 1, "§7---------------------");
			ScoreFactory::setScoreLine($p, 2, " §bPlayers: §f" . count($this->plugin->getGamePlayers()));
			ScoreFactory::setScoreLine($p, 3, $this->getPhase() === PhaseChangeEvent::WAITING ? "§b Waiting for players..." : "§b Starting in:§f $this->countdown");
			ScoreFactory::setScoreLine($p, 4, "§7--------------------- ");
		}
	}

	private function randomizeCoordinates(Player $p, int $range): void
	{
		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($p, $range) : void {
			$ss = $p->getWorld()->getSafeSpawn();
			$x = mt_rand($ss->getFloorX() - $range, $ss->getFloorX() + $range);
			$z = mt_rand($ss->getFloorZ() - $range, $ss->getFloorZ() + $range);

			ChunkRegion::onChunkGenerated($p->getWorld(), $x >> 4, $z >> 4, function () use ($p, $x, $z): void {
				$p->teleport(new Vector3($x, $p->getWorld()->getHighestBlockAt($x, $z) + 1, $z));
			});

			$this->playerTimer += 5;
		}), $this->playerTimer);
	}
}
