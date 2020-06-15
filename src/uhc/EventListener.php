<?php

declare(strict_types=1);

namespace uhc;

use JackMD\ScoreFactory\ScoreFactory;
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
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\event\PhaseChangeEvent;

class EventListener implements Listener
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }

    public function handleChat(PlayerChatEvent $ev): void
    {
        $player = $ev->getPlayer();
        if ($this->plugin->isGlobalMuteEnabled() && !$player->isOp()) {
            $player->sendMessage(TF::RED . "You cannot talk right now!");
            $ev->setCancelled();
        }
    }

    public function handleJoin(PlayerJoinEvent $ev): void
    {
        $player = $ev->getPlayer();
        if (!$this->plugin->hasSession($player)) {
            $this->plugin->addSession(PlayerSession::create($player));
        } else {
            $this->plugin->getSession($player)->setPlayer($player);
        }

        if ($this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::WAITING) {
            $player->teleport($player->getLevel()->getSafeSpawn());
            $player->setGamemode(Player::SURVIVAL);
        }

        $ev->setJoinMessage("");
    }

    public function handlePhaseChange(PhaseChangeEvent $ev): void
    {
        $player = $ev->getPlayer();
        if ($ev->getOldPhase() === PhaseChangeEvent::COUNTDOWN) {
            $player->getInventory()->addItem(ItemFactory::get(ItemIds::STEAK, 0, 64));
        }
    }

    public function handleQuit(PlayerQuitEvent $ev): void
    {
        $player = $ev->getPlayer();
        $this->plugin->removeFromGame($player);
        ScoreFactory::removeScore($player);
        $ev->setQuitMessage("");
    }

    public function handleEntityRegain(EntityRegainHealthEvent $ev): void
    {
        if ($ev->getRegainReason() === EntityRegainHealthEvent::CAUSE_SATURATION) {
            $ev->setCancelled();
        }
    }

    public function handleDamage(EntityDamageEvent $ev): void
    {
        if (
            !$this->plugin->getHeartbeat()->hasStarted() ||
            (
                $this->plugin->getHeartbeat()->getPhase() === PhaseChangeEvent::GRACE &&
                $ev instanceof EntityDamageByEntityEvent
            )
        ) {
            $ev->setCancelled();
        }
    }

    public function handleDeath(PlayerDeathEvent $ev): void
    {
        $player = $ev->getPlayer();
        $cause = $player->getLastDamageCause();
        $eliminatedSession = $this->plugin->getSession($player);
        $player->setGamemode(3);
        $player->addTitle(TF::YELLOW . "You have been eliminated!", "Use /spectate to spectate a player.");
        if ($cause instanceof EntityDamageByEntityEvent) {
            $damager = $cause->getDamager();
            if ($damager instanceof Player) {
                $damagerSession = $this->plugin->getSession($damager);
                $damagerSession->addElimination();
                $ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $eliminatedSession->getEliminations() . TF::GRAY . "]" . TF::YELLOW . " was slain by " . TF::RED . $damager->getName() . TF::GRAY . "[" . TF::WHITE . $damagerSession->getEliminations() . TF::GRAY . "]");
            }
        } else {
            $ev->setDeathMessage(TF::RED . $player->getName() . TF::GRAY . "[" . TF::WHITE . $eliminatedSession->getEliminations() . TF::GRAY . "]" . TF::YELLOW . " died!");
        }
    }

    public function handleBreak(BlockBreakEvent $ev): void
    {
        if (!$this->plugin->getHeartbeat()->hasStarted()) {
            $ev->setCancelled();
        }
    }

    public function handlePlace(BlockPlaceEvent $ev): void
    {
        if (!$this->plugin->getHeartbeat()->hasStarted()) {
            $ev->setCancelled();
        }
    }
}
