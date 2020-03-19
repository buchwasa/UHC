<?php

namespace uhc;

use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityRegainHealthEvent;
use pocketmine\event\level\LevelLoadEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\network\mcpe\protocol\LevelSoundEventPacket;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\event\UHCStartEvent;

class EventListener implements Listener{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		$this->plugin = $plugin;
		$plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
	}

	public function handleReceive(DataPacketReceiveEvent $ev) : void{
		$packet = $ev->getPacket();
		if($packet instanceof LevelSoundEventPacket){
			if($packet->sound === LevelSoundEventPacket::SOUND_ATTACK_NODAMAGE || $packet->sound === LevelSoundEventPacket::SOUND_ATTACK_STRONG){
				$ev->setCancelled();
			}
		}
	}

	public function handleLoad(LevelLoadEvent $ev) : void{
		$ev->getLevel()->setTime(7000);
		$ev->getLevel()->stopTime();
	}

	public function handleChat(PlayerChatEvent $ev) : void{
		$player = $ev->getPlayer();
		if($this->plugin->globalMute && !$player->isOp()){
			$player->sendMessage(TF::RED . "You cannot talk right now!");
			$ev->setCancelled(true);
		}elseif($player->getGamemode() === Player::SPECTATOR){
			$ev->setFormat(TF::GRAY . "SPEC " . TF::WHITE . $player->getName() . TF::GRAY . ": " . TF::WHITE . $ev->getMessage());
		}
	}

	public function handleJoin(PlayerJoinEvent $ev) : void{
		$player = $ev->getPlayer();

		$pk = new GameRulesChangedPacket();
		$pk->gameRules = ["showcoordinates" => [1, true], "immediaterespawn" => [1, true]];
		$player->dataPacket($pk);

		$ev->setJoinMessage("");
	}

	public function handleStart(UHCStartEvent $ev) : void{
		foreach($ev->getPlayers() as $player){
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::STEAK, 0, 64));
			$player->getInventory()->addItem(ItemFactory::get(ItemIds::LEATHER, 0, 32));
		}
	}

	public function handleQuit(PlayerQuitEvent $ev) : void{
		$player = $ev->getPlayer();
		//TODO: View the necessity of this.
		if(isset($this->plugin->queue[$player->getName()])){
			unset($this->plugin->queue[$player->getName()]);
		}
		$ev->setQuitMessage("");
	}

	public function handleEntityRegain(EntityRegainHealthEvent $ev) : void{
		if($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION){
			$ev->setCancelled();
		}
	}

	public function handleDamage(EntityDamageEvent $ev) : void{
		switch(UHCTimer::$gameStatus){
			case UHCTimer::STATUS_WAITING:
			case UHCTimer::STATUS_COUNTDOWN:
				$ev->setCancelled();
				break;
			case UHCTimer::STATUS_GRACE:
				if($ev instanceof EntityDamageByEntityEvent){
					$ev->setCancelled();
				}
				break;
		}
	}

	public function handleDeath(PlayerDeathEvent $ev) : void{
		$player = $ev->getPlayer();
		$cause = $player->getLastDamageCause();
		$player->setGamemode(3);
		$player->sendMessage(TF::YELLOW . "You have been eliminated, use /spectate to spectate a player!");
		$player->getLevel()->broadcastLevelSoundEvent($player, LevelSoundEventPacket::SOUND_RAID_HORN);
		if($cause instanceof EntityDamageByEntityEvent){
			$damager = $cause->getDamager();
			if($damager instanceof Player){
				$this->plugin->addElimination($damager);
				$ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $this->plugin->getEliminations($player) . TF::GRAY . "]" . TF::YELLOW . " was slain by " . TF::RED . $damager->getName() . TF::GRAY . "[" . TF::WHITE . $this->plugin->getEliminations($damager) . TF::GRAY . "]");
			}
		}else{
			$ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $this->plugin->getEliminations($player) . TF::GRAY . "]" . TF::YELLOW . " died!");
		}
	}

	public function handleBreak(BlockBreakEvent $ev) : void{
		$player = $ev->getPlayer();
		if($player instanceof Player){
			switch(UHCTimer::$gameStatus){
				case UHCTimer::STATUS_WAITING:
				case UHCTimer::STATUS_COUNTDOWN:
					$ev->setCancelled();
					break;
			}
		}
	}

	public function handlePlace(BlockPlaceEvent $ev) : void{
		$player = $ev->getPlayer();
		if($player instanceof Player){
			switch(UHCTimer::$gameStatus){
				case UHCTimer::STATUS_WAITING:
				case UHCTimer::STATUS_COUNTDOWN:
					$ev->setCancelled();
					break;
			}
		}
	}
}
