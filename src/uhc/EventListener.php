<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerExhaustEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerLoginEvent;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\types\BoolGameRule;
use pocketmine\utils\TextFormat as TF;
use pocketmine\item\VanillaItems;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\block\Leaves;
use pocketmine\event\Listener;

use uhc\event\PhaseChangeEvent;

class EventListener implements Listener
{
	/** @var UHC */
	private UHC $plugin;

	public function __construct(UHC $plugin)
	{
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function handleChat(PlayerChatEvent $event): void
	{
		$player = $event->getPlayer();
		if ($this->plugin->isGlobalMuteEnabled() && !$player->hasPermission("uhc.bypass.globalmute")) {
			$player->sendMessage(TF::RED . "You cannot talk right now!");
			$event->cancel();
		}
	}

	public function handleLogin(PlayerLoginEvent $event): void
	{
		$player = $event->getPlayer();
		$sessionManager = $this->plugin->getSessionManager();
		if ($this->plugin->getHeartbeat()->getPhase() >= PhaseChangeEvent::COUNTDOWN && !$sessionManager->hasSession($player)) {
			$event->setKickMessage("UHC has already started!");
			$event->cancel();
		}
		$sessionManager->createSession($player);
		$sessionManager->getSession($player)->setPlaying(true);
	}

	public function handleJoin(PlayerJoinEvent $event): void
	{
		if ($this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::WAITING) {
			$this->plugin->resetPlayer($event->getPlayer(), true);
		}
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = ["showcoordinates" => new BoolGameRule(true)];
		$event->getPlayer()->getNetworkSession()->sendDataPacket($pk);
	}

	public function handlePhaseChange(PhaseChangeEvent $event): void
	{
		if ($event->getOldPhase() === PhaseChangeEvent::COUNTDOWN) {
			$event->getPlayer()->getInventory()->addItem(VanillaItems::STEAK()->setCount(64));
		}
	}

	public function handleQuit(PlayerQuitEvent $event): void
	{
		$this->plugin->getSessionManager()->getSession($event->getPlayer())->setPlaying(false);
	}

	public function handleEntityRegain(EntityRegainHealthEvent $event): void
	{
		if ($event->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION) {
			$event->cancel();
		}
	}

	public function handleDamage(EntityDamageEvent $event): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$event->cancel();
		}

		if ($event instanceof EntityDamageByEntityEvent) {
			$damager = $event->getDamager();
			$victim = $event->getEntity();
			if ($this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::GRACE) {
				$event->cancel();
			}

			if ($damager instanceof Player && $victim instanceof Player) {
				$damagerSession = $this->plugin->getSessionManager()->getSession($damager);
				$victimSession = $this->plugin->getSessionManager()->getSession($victim);
				if ($damagerSession->isInTeam() && $victimSession->isInTeam() && $damagerSession->getTeam()->memberExists($victim)) {
					$event->cancel();
				}
			}
		}
	}

	public function handleDeath(PlayerDeathEvent $event): void
	{
		$player = $event->getPlayer();
		$cause = $player->getLastDamageCause();
		$eliminatedSession = $this->plugin->getSessionManager()->getSession($player);
		$player->setGamemode(GameMode::SPECTATOR());
		$player->sendTitle(TF::AQUA . "You have been eliminated!", "Use /spectate to spectate a player.");
		$eliminatedSession->setPlaying(false);

		if ($cause instanceof EntityDamageByEntityEvent) {
			$damager = $cause->getDamager();
			if ($damager instanceof Player) {
				$damagerSession = $this->plugin->getSessionManager()->getSession($damager);
				$deathMessages = $this->plugin->getMessageManager()->getDeathMessages();
				$eliminations = $eliminatedSession->getEliminations();
				$damagerEliminations = $damagerSession->getEliminations();
				$damagerSession->addEliminations();

				$event->setDeathMessage(
					TF::RED . $player->getName() . TF::GRAY . "[" . $eliminations . TF::GRAY . "]" .
					TF::AQUA . $deathMessages[array_rand($deathMessages)] . TF::RED . $damager->getName() .
					TF::GRAY . "[" . $damagerEliminations . TF::GRAY . "]",
				);
			}
		} else {
			$event->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $eliminatedSession->getEliminations() . TF::GRAY . "]" . TF::YELLOW . " died!");
		}
	}

	public function handleBreak(BlockBreakEvent $event): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$event->cancel();
		} else {
			if ($event->getBlock() instanceof Leaves) {
				$rand = mt_rand(0, 100);
				if ($event->getItem()->equals(VanillaItems::SHEARS(), false, false)) {
					if ($rand <= 6) {
						$event->setDrops([VanillaItems::APPLE()]);
					}
				} else {
					if ($rand <= 3) {
						$event->setDrops([VanillaItems::APPLE()]);
					}
				}
			}
		}
	}

	public function handlePlace(BlockPlaceEvent $event): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$event->cancel();
		}
	}

	public function handleItemDrop(PlayerDropItemEvent $event): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$event->cancel();
		}
	}

	public function handleExhaust(PlayerExhaustEvent $event): void
	{
		if (!$this->plugin->getHeartbeat()->hasStarted()) {
			$event->cancel();
		}
	}
}
