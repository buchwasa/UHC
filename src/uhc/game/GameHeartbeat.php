<?php

declare(strict_types=1);

namespace uhc\game;

use jackmd\scorefactory\ScoreFactory;
use pocketmine\player\Player;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use uhc\event\PhaseChangeEvent;
use uhc\game\type\GameTimer;
use uhc\Loader;

class GameHeartbeat extends Task
{
	/** @var int */
	private int $phase = PhaseChangeEvent::WAITING;

	/** @var int */
	private int $game = 0;
	/** @var int */
	private int $countdown = GameTimer::TIMER_COUNTDOWN;
	/** @var float|int */
	private $grace = GameTimer::TIMER_GRACE;
	/** @var float|int */
	private $pvp = GameTimer::TIMER_PVP;
	/** @var float|int */
	private $normal = GameTimer::TIMER_NORMAL;
	/** @var Border */
	private Border $border;
	/** @var Loader */
	private Loader $plugin;

	public function __construct(Loader $plugin)
	{
		$this->plugin = $plugin;
		$this->border = new Border($plugin->getServer()->getWorldManager()->getDefaultWorld());
	}

	public function getBorder() : Border
	{
		return $this->border;
	}

	public function getPhase(): int
	{
		return $this->phase;
	}

	public function setPhase(int $phase): void
	{
		foreach ($this->plugin->getSessionManager()->getPlaying() as $playerSession) {
			$ev = new PhaseChangeEvent($playerSession->getPlayer(), $this->phase, $phase);
			$ev->call();
		}
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
		foreach ($this->plugin->getSessionManager()->getSessions() as $session) {
			$p = $session->getPlayer();
			if($p->isOnline()) {
				$this->handleScoreboard($p);
				if($session->isPlaying()) {
					$name = $session->getTeam() !== null ? (string)$session->getTeam()->getNumber() : "NO TEAM";
					$p->setNameTag(TF::GOLD . "[$name] " . TF::WHITE . $p->getDisplayName());
					if (!$this->border->isPlayerInsideOfBorder($p)) {
						$this->border->teleportPlayer($p);
						$p->sendTitle("You have been teleported by border!");
					}
					switch ($this->getPhase()) {
						case PhaseChangeEvent::COUNTDOWN:
							if ($this->countdown === 29) {
								$this->plugin->resetPlayer($p);
								$p->setImmobile(true);
							} elseif ($this->countdown === 3) {
								$p->setImmobile(false);
							}
							break;
						case PhaseChangeEvent::GRACE:
							if ($this->grace === 601) {
								$p->setHealth($p->getMaxHealth());
							}
							break;
					}
				}
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
			ScoreFactory::setScoreLine($p, 3, " §bRemaining: §f" . count($this->plugin->getSessionManager()->getPlaying()));
			ScoreFactory::setScoreLine($p, 4, " §bEliminations: §f" . $this->plugin->getSessionManager()->getSession($p)->getEliminations());
			ScoreFactory::setScoreLine($p, 5, " §bBorder: §f" . $this->border->getSize());
			ScoreFactory::setScoreLine($p, 6, " §bCenter: §f({$safeSpawn->getFloorX()}, {$safeSpawn->getFloorZ()})");
			ScoreFactory::setScoreLine($p, 7, "§7--------------------- ");
		} else {
			ScoreFactory::setScoreLine($p, 1, "§7---------------------");
			ScoreFactory::setScoreLine($p, 2, " §bPlayers: §f" . count($this->plugin->getSessionManager()->getSessions()));
			ScoreFactory::setScoreLine($p, 3, $this->getPhase() === PhaseChangeEvent::WAITING ? "§b Waiting for players..." : "§b Starting in:§f $this->countdown");
			ScoreFactory::setScoreLine($p, 4, "§7--------------------- ");
		}
	}
}
