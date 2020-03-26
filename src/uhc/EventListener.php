<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\event\UHCStartEvent;

class EventListener implements Listener {

	/** @var Loader */
	private $plugin;

	/**
	 * EventListener constructor.
	 * @param Loader $plugin
	 */
	public function __construct(Loader $plugin) {
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	/**
	 * @return Loader
	 */
	public function getPlugin(): Loader {
		return $this->plugin;
	}

	/**
	 * @param PlayerChatEvent $ev
	 */
	public function handleChat(PlayerChatEvent $ev): void {
		$player = $ev->getPlayer();
		if($this->getPlugin()->isGlobalMuteEnabled() && !$player->isOp()){
			$player->sendMessage(TF::RED . "You cannot talk right now!");
			$ev->setCancelled(true);
		}elseif($player->getGamemode() === Player::SPECTATOR){
			$ev->setFormat(TF::GRAY . "SPEC " . TF::WHITE . $player->getName() . TF::GRAY . ": " . TF::WHITE . $ev->getMessage());
		}
	}

	/**
	 * @param PlayerJoinEvent $ev
	 */
	public function handleJoin(PlayerJoinEvent $ev): void {
		$player = $ev->getPlayer();
		if(!$this->getPlugin()->hasSession($player)) {
			$this->getPlugin()->addSession(PlayerSession::create($player));
		} else {
			/* Updates player instance in PlayerSession */
			$session = $this->getPlugin()->getSession($player);
			$session->setPlayer($player);
		}
		if($this->getPlugin()->getHeartbeat()->getGameStatus() === GameHeartbeat::STATUS_WAITING) {
			$player->teleport($player->getLevel()->getSafeSpawn());
			$player->setGamemode(Player::SURVIVAL);
		}
		$pk = new GameRulesChangedPacket();
		$pk->gameRules = ["showcoordinates" => [1, true], "immediaterespawn" => [1, true]];
		$player->dataPacket($pk);
		$ev->setJoinMessage(null);
	}

	/**
	 * @param UHCStartEvent $ev
	 */
	public function handleStart(UHCStartEvent $ev): void {
		foreach($ev->getPlayers() as $player){
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::STEAK, 0, 64));
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::LEATHER, 0, 32));
		}
	}

	/**
	 * @param PlayerQuitEvent $ev
	 */
	public function handleQuit(PlayerQuitEvent $ev): void {
		$player = $ev->getPlayer();
		//TODO: View the necessity of this.
		$this->getPlugin()->removeFromGame($player);
		/* Updates player instance in PlayerSession */
		$session = $this->getPlugin()->getSession($player);
		$session->getScoreboard()->remove();
		$ev->setQuitMessage(null);
	}

	/**
	 * @param EntityRegainHealthEvent $ev
	 */
	public function handleEntityRegain(EntityRegainHealthEvent $ev): void {
		if($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION){
			$ev->setCancelled();
		}
	}

	/**
	 * @param EntityDamageEvent $ev
	 */
	public function handleDamage(EntityDamageEvent $ev): void {
		switch($this->getPlugin()->getHeartbeat()->getGameStatus()){
			case GameHeartbeat::STATUS_WAITING:
			case GameHeartbeat::STATUS_COUNTDOWN:
				$ev->setCancelled();
				break;
			case GameHeartbeat::STATUS_GRACE:
				if($ev instanceof EntityDamageByEntityEvent){
					$ev->setCancelled();
				}
				break;
		}
	}

	/**
	 * @param PlayerDeathEvent $ev
	 */
	public function handleDeath(PlayerDeathEvent $ev): void {
		$player = $ev->getPlayer();
		$cause = $player->getLastDamageCause();
		$player->setGamemode(3);
		$player->addTitle(TF::YELLOW . "You have been eliminated!", "Use /spectate to spectate a player.");
		$player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_RAID_HORN);
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player) {
				$this->getPlugin()->addElimination($damager);
				$ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $this->getPlugin()->getEliminations($player) . TF::GRAY . "]" . TF::YELLOW . " was slain by " . TF::RED . $damager->getName() . TF::GRAY . "[" . TF::WHITE . $this->getPlugin()->getEliminations($damager) . TF::GRAY . "]");
			}
		}else{
			$ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $this->getPlugin()->getEliminations($player) . TF::GRAY . "]" . TF::YELLOW . " died!");
		}
	}

	/**
	 * @param BlockBreakEvent $ev
	 */
	public function handleBreak(BlockBreakEvent $ev) : void {
		$player = $ev->getPlayer();
		if($player instanceof Player){
			if(!$this->getPlugin()->getHeartbeat()->hasStarted()) {
				$ev->setCancelled();
			}
		}
	}

	/**
	 * @param BlockPlaceEvent $ev
	 */
	public function handlePlace(BlockPlaceEvent $ev): void {
		$player = $ev->getPlayer();
		if($player instanceof Player){
			if(!$this->getPlugin()->getHeartbeat()->hasStarted()) {
				$ev->setCancelled();
			}
		}
	}
}
