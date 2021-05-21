<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\block\Leaves;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\VanillaItems;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\event\PhaseChangeEvent;
use uhc\game\GameProperties;

class EventListener implements Listener
{
	/** @var Loader */
	private Loader $plugin;

	public function __construct(Loader $plugin)
	{
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function handleChat(PlayerChatEvent $ev): void
	{
		$player = $ev->getPlayer();
		if (GameProperties::$globalMute) {
			$player->sendMessage(TF::RED . "You cannot talk right now!");
			$ev->cancel();
		}
	}

	public function handleLogin(PlayerLoginEvent $ev): void
	{
		$player = $ev->getPlayer();
		$sessionManager = $this->plugin->getSessionManager();
		if ($this->plugin->getHeartbeat()->getPhase() >= PhaseChangeEvent::COUNTDOWN && !$sessionManager->hasSession($player)) {
			$ev->setKickMessage("UHC has already started!");
			$ev->cancel();
		}
		$sessionManager->createSession($player);
		$sessionManager->getSession($player)->setPlaying(true);
	}

	public function handleJoin(PlayerJoinEvent $ev): void
	{
		if ($this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::WAITING) {
			$this->plugin->resetPlayer($ev->getPlayer(), true);
		}
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = ["showcoordinates" => new BoolGameRule(true)];
		$ev->getPlayer()->getNetworkSession()->sendDataPacket($pk);
	}

	public function handlePhaseChange(PhaseChangeEvent $ev): void
	{
		if ($ev->getOldPhase() === PhaseChangeEvent::COUNTDOWN) {
			$ev->getPlayer()->getInventory()->addItem(VanillaItems::STEAK()->setCount(64));
		}
	}

	public function handleQuit(PlayerQuitEvent $ev): void
	{
		$this->plugin->getSessionManager()->getSession($ev->getPlayer())->setPlaying(false);
	}

	public function handleEntityRegain(EntityRegainHealthEvent $ev): void
	{
		if ($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION) {
			$ev->cancel();
		}
	}

	public function handleDamage(EntityDamageEvent $ev): void
	{
		$victim = $ev->getEntity();
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$ev->cancel();
		}

		if ($ev instanceof EntityDamageByEntityEvent) {
			$damager = $ev->getDamager();
			if ($this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::GRACE) {
				$ev->cancel();
			}

			if ($damager instanceof Player && $victim instanceof Player) {
				$damagerSession = $this->plugin->getSessionManager()->getSession($damager);
				$victimSession = $this->plugin->getSessionManager()->getSession($victim);
				if ($damagerSession->isInTeam() && $victimSession->isInTeam() && $damagerSession->getTeam()->memberExists($victim)) {
					$ev->cancel();
				}
			}
		}

		if (!$ev->isCancelled() && $ev->getFinalDamage() >= $victim->getHealth()) {
			//TODO: Clean up this mess
			$ev->cancel();
			$eliminatedSession = $this->plugin->getSessionManager()->getSession($victim);
			$eliminatedSession->setPlaying(false);
			if ($ev instanceof EntityDamageByEntityEvent) {
				$damager = $ev->getDamager();
				if ($damager instanceof Player) {
					$damagerSession = $this->plugin->getSessionManager()->getSession($damager);
					$damagerSession->addEliminations();
					//TODO: Introduce world based broadcast methods
					foreach ($victim->getWorld()->getPlayers() as $player) {
						$player->sendMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $eliminatedSession->getEliminations() . TF::GRAY . "]" . TF::YELLOW . " was slain by " . TF::RED . $damager->getName() . TF::GRAY . "[" . TF::WHITE . $damagerSession->getEliminations() . TF::GRAY . "]");
					}
				}
			} else {
				foreach ($victim->getWorld()->getPlayers() as $player) {
					$player->sendMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $eliminatedSession->getEliminations() . TF::GRAY . "]" . TF::YELLOW . " died!");
				}
			}

			$this->plugin->resetPlayer($victim);
			$victim->setGamemode(GameMode::SPECTATOR());
		}
	}

	public function handleBreak(BlockBreakEvent $ev): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$ev->cancel();
		} else {
			if ($ev->getBlock() instanceof Leaves) {
				$rand = mt_rand(0, 100);
				if ($ev->getItem()->equals(VanillaItems::SHEARS(), false, false)) {
					if ($rand <= 6) {
						$ev->setDrops([VanillaItems::APPLE()]);
					}
				} else {
					if ($rand <= 3) {
						$ev->setDrops([VanillaItems::APPLE()]);
					}
				}
			}
		}
	}

	public function handlePlace(BlockPlaceEvent $ev): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$ev->cancel();
		}
	}

	public function handleItemDrop(PlayerDropItemEvent $ev): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$ev->cancel();
		}
	}

	public function handleExhaust(PlayerExhaustEvent $ev): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$ev->cancel();
		}
	}
}
