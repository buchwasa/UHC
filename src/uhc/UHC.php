<?php

declare(strict_types=1);

namespace uhc;

use pocketmine\permission\DefaultPermissions;
use pocketmine\permission\Permission;
use pocketmine\player\GameMode;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use uhc\command\GlobalMuteCommand;
use uhc\command\HealCommand;
use uhc\command\ScenariosCommand;
use uhc\command\SpectateCommand;
use uhc\command\TeamCommand;
use uhc\command\TpallCommand;
use uhc\command\UHCCommand;
use uhc\game\GameHeartbeat;
use uhc\game\MessageManager;
use uhc\game\scenario\ScenarioManager;
use uhc\game\team\TeamManager;
use uhc\session\SessionManager;

class UHC extends PluginBase
{
	public const ROOT = "uhc";
	/** @var GameHeartbeat */
	private GameHeartbeat $heartbeat;
	/** @var ScenarioManager */
	private ScenarioManager $scenarioManager;
	/** @var SessionManager */
	private SessionManager $sessionManager;
	/** @var TeamManager */
	private TeamManager $teamManager;
	/** @var MessageManager */
	private MessageManager $messageManager;
	/** @var bool */
	private bool $globalMuteEnabled = false;
	/** @var Config */
	public Config $config;

	private static $instance;

	public function onEnable(): void
	{
		self::$instance = $this;

		$this->registerPermissions();
		$this->getScheduler()->scheduleRepeatingTask($this->heartbeat = new GameHeartbeat($this), 20);
		$this->scenarioManager = new ScenarioManager($this);
		$this->messageManager = new MessageManager($this);
		$this->sessionManager = new SessionManager();
		$this->teamManager = new TeamManager();
		new EventListener($this);

		$this->getServer()->getCommandMap()->registerAll("uhc", [
			new UHCCommand($this),
			new ScenariosCommand($this),
			new SpectateCommand($this),
			new HealCommand($this),
			new GlobalMuteCommand($this),
			new TeamCommand($this),
			new TpallCommand($this)
		]);

		$this->config = new Config($this->getDataFolder() . "Config.json", Config::JSON);
	}

	public static function getInstance(): UHC{
		return self::$instance;
	}

	public function getHeartbeat(): GameHeartbeat
	{
		return $this->heartbeat;
	}

	public function getScenarioManager(): ScenarioManager
	{
		return $this->scenarioManager;
	}

	public function getSessionManager(): SessionManager
	{
		return $this->sessionManager;
	}

	public function getTeamManager(): TeamManager
	{
		return $this->teamManager;
	}

	public function getMessageManager(): MessageManager
	{
		return $this->messageManager;
	}

	public function setGlobalMute(bool $enabled): void
	{
		$this->globalMuteEnabled = $enabled;
	}

	public function isGlobalMuteEnabled(): bool
	{
		return $this->globalMuteEnabled;
	}

	public function resetPlayer(Player $player, bool $fullReset = false): void
	{
		$player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
		$player->setHealth($player->getMaxHealth());
		$player->getXpManager()->setXpAndProgress(0, 0.0);
		$player->getEffects()->clear();
		$player->getInventory()->clearAll();
		$player->getArmorInventory()->clearAll();
		$player->getCursorInventory()->clearAll();
		if ($fullReset) {
			$player->teleport($player->getWorld()->getSafeSpawn());
			$player->setGamemode(GameMode::SURVIVAL());
		}
	}

	public function registerPermissions(): void
	{
		$parent = DefaultPermissions::registerPermission(new Permission(self::ROOT, "Parent for all UHC permissions."));

		$commands = DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command", "Parent permission for all UHC commands."), [$parent]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command.globalmute", ""), [$commands]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command.heal",""), [$commands]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command.scenarios", ""), [$commands]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command.tpall", ""), [$commands]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".command.uhc", ""), [$commands]);
		$commands->recalculatePermissibles();

		$bypass = DefaultPermissions::registerPermission(new Permission(self::ROOT . ".bypass", "Parent permission for all UHC bypasses"), [$parent]);
		DefaultPermissions::registerPermission(new Permission(self::ROOT . ".bypass.globalmute", ""), [$bypass]);
		$bypass->recalculatePermissibles();

		$parent->recalculatePermissibles();
	}
}
