<?php

namespace uhc;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat as TF;
use uhc\event\UHCStartEvent;
use uhc\utils\Border;
use uhc\utils\RegionUtils;
use uhc\utils\Scoreboard;
use function mt_rand;
use function round;

class UHCTimer extends Task{
	/** @var int */
	public static $gameStatus = self::STATUS_WAITING;

	/** @var int */
	public const STATUS_WAITING = -1;
	/** @var int */
	public const STATUS_COUNTDOWN = 0;
	/** @var int */
	public const STATUS_GRACE = 1;
	/** @var int */
	public const STATUS_PVP = 2;
	/** @var int */
	public const STATUS_NORMAL = 3;

	/** @var int */
	private $game = 0;
	/** @var int */
	private $countdown = 30;
	/** @var float|int */
	private $grace = 60 * 20;
	/** @var float|int */
	private $pvp = 60 * 30;
	/** @var float|int */
	private $normal = 60 * 60;
	/** @var Border */
	private $border;
	/** @var Loader */
	private $plugin;

	/** @var int */
	private $playerTimer = 1;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
		$this->border = new Border($plugin->getServer()->getDefaultLevel());
	}

	public function onRun(int $currentTick) : void{
		$this->handlePlayers();

		if(self::$gameStatus >= self::STATUS_GRACE) $this->game++;
		switch(self::$gameStatus){
			case self::STATUS_COUNTDOWN:
				$this->handleCountdown();
				break;
			case self::STATUS_GRACE:
				$this->handleGrace();
				break;
			case self::STATUS_PVP:
				$this->handlePvP();
				break;
			case self::STATUS_NORMAL:
				$this->handleNormal();
				break;
		}
	}

	private function handlePlayers() : void{
		foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
			if($p->isSurvival()){
				$this->plugin->addToGame($p);
			}else{
				$this->plugin->removeFromGame($p);
			}

			$this->handleScoreboard($p);
		}

		foreach($this->plugin->getGamePlayers() as $player){
			$player->setScoreTag(floor($player->getHealth()) . TF::RED . " ❤");
			if(!$this->border->isPlayerInsideOfBorder($player)){
                $this->border->teleportPlayer($player);
			}
			switch(self::$gameStatus){
				case self::STATUS_COUNTDOWN:
					$player->setFood($player->getMaxFood());
					$player->setHealth($player->getMaxHealth());
					if($this->countdown === 29){
						$this->randomizeCoordinates($player, 750);
						$player->setWhitelisted(true);
						$player->removeAllEffects();
						$player->getInventory()->clearAll();
						$player->getArmorInventory()->clearAll();
						$player->getCursorInventory()->clearAll();
						$player->setImmobile(true);
					}elseif($this->countdown === 3){
						$player->setImmobile(false);
					}
					break;
				case self::STATUS_GRACE:
					if($this->grace === 601){
						$player->setHealth($player->getMaxHealth());
					}
					break;
			}
		}
	}

	private function handleCountdown() : void{
		$this->countdown--;
		$server = $this->plugin->getServer();
		switch($this->countdown){
			case 29:
				$server->setConfigBool("white-list", true);
				$server->broadcastTitle("Server has been " . TF::AQUA . "whitelisted!");
				$server->broadcastTitle("The game will begin in " . TF::AQUA . "30 seconds.");
				break;
			case 28:
				$server->broadcastTitle("Global Mute has been " . TF::AQUA . "enabled!");
				$this->plugin->setGlobalMute(true);
				break;
			/*case 23:
				$scenarios = [];
				foreach($this->plugin->getScenarios() as $scenario){
					if($scenario->isActive()){
						$scenarios[] = $scenario->getName();
					}
				}
				$server->broadcastTitle("The scenario for this game are:\n" . TF::AQUA . (count($scenarios) > 0 ? implode("\n", $scenarios) : "None"));
				break;*/
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
				foreach($this->plugin->getServer()->getDefaultLevel()->getEntities() as $entity){
					if(!$entity instanceof Player){
						$entity->flagForDespawn();
					}
				}
				$ev = new UHCStartEvent($this->plugin->getGamePlayers());
				$ev->call();
				$server->broadcastTitle(TF::RED . TF::BOLD . "The UHC has begun!");
				self::$gameStatus = self::STATUS_GRACE;
				$this->countdown = 30;
				break;
		}
	}

	private function handleGrace() : void{
		$this->grace--;
		$server = $this->plugin->getServer();
		switch($this->grace){
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
				$server->broadcastTitle(TF::RED . "PvP has been enabled, good luck!");
				self::$gameStatus = self::STATUS_PVP;
				$this->grace = 60 * 20;
				break;
		}
	}

	private function handlePvP() : void{
		$this->pvp--;
		$server = $this->plugin->getServer();
		switch($this->pvp){
			case 900:
				$server->broadcastTitle("The border will shrink to " . TF::AQUA . "750" . TF::WHITE . " in " . TF::AQUA . "5 minutes");
				break;
			case 600:
				$this->border->setSize(750);
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".\nShrinking to " . TF::AQUA . "500" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				break;
			case 300:
				$this->border->setSize(500);
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".\nShrinking to " . TF::AQUA . "250" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				break;
			case 0:
				$this->border->setSize(250);
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".\nShrinking to " . TF::AQUA . "100" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				self::$gameStatus = self::STATUS_NORMAL;
				$this->pvp = 60 * 30;
				break;
		}
	}

	public function handleNormal() : void{
		$this->normal--;
		$server = $this->plugin->getServer();
		switch($this->normal){
			case 3300:
				$this->border->setSize(100);
				$this->border->build();
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".\nShrinking to " . TF::AQUA . "25" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				break;
			case 3000:
				$this->border->setSize(25);
				$this->border->build();
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".\nShrinking to " . TF::AQUA . "10" . TF::WHITE . " in " . TF::AQUA . "5 minutes.");
				break;
			case 2700:
				$this->border->setSize(10);
				$this->border->build();
				$server->broadcastTitle("The border has shrunk to " . TF::AQUA . $this->border . ".");
				break;
		}
	}

	private function handleScoreboard(Player $p) : void{
		Scoreboard::setTitle($p, "§ky§r §b" . $p->getDisplayName() . " §f§ky§r");

		if(self::$gameStatus >= self::STATUS_GRACE){
			Scoreboard::setLine($p, 1, "§7---------------------");
			Scoreboard::setLine($p, 2, " §bGame Time: §f" . gmdate("H:i:s", $this->game));
			Scoreboard::setLine($p, 3, " §bRemaining: §f" . count($this->plugin->getGamePlayers()));
			Scoreboard::setLine($p, 4, " §bEliminations: §f" . $this->plugin->getEliminations($p));
			Scoreboard::setLine($p, 5, " §bBorder: §f" . $this->border->getSize());
			Scoreboard::setLine($p, 6, " §bCenter: §f(" . $p->getLevel()->getSafeSpawn()->getFloorX() . ", " . $p->getLevel()->getSafeSpawn()->getFloorZ() . ")");
			Scoreboard::setLine($p, 7, "§7--------------------- ");
		}elseif(self::$gameStatus <= self::STATUS_COUNTDOWN){
			Scoreboard::setLine($p, 1, "§7---------------------");
			Scoreboard::setLine($p, 2, " §bPlayers: §f" . count($this->plugin->getGamePlayers()));
			if(self::$gameStatus === self::STATUS_WAITING){
				Scoreboard::setLine($p, 3, "§b Waiting for players...");
			}else{
				Scoreboard::setLine($p, 3, "§b Starting in:§f $this->countdown");
			}
			Scoreboard::setLine($p, 4, "§7--------------------- ");
		}
	}

	private function randomizeCoordinates(Player $p, int $range) : void{
		$this->plugin->getScheduler()->scheduleDelayedTask(new ClosureTask(function(int $currentTick) use ($p, $range) : void{
			$ss = $p->getLevel()->getSafeSpawn();
			$x = mt_rand($ss->getFloorX() - $range, $ss->getFloorX() + $range);
			$z = mt_rand($ss->getFloorZ() - $range, $ss->getFloorZ() + $range);

			RegionUtils::onChunkGenerated($p->getLevel(), $x >> 4, $z >> 4, function() use ($p, $x, $z){
				$p->teleport(new Vector3($x, $p->getLevel()->getHighestBlockAt($x, $z) + 1, $z));
			});

			$this->playerTimer += 5;
		}), $this->playerTimer);
	}
}
