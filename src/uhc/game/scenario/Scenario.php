<?php

declare(strict_types=1);

namespace uhc\game\scenario;

use pocketmine\event\HandlerListManager;
use pocketmine\event\Listener;
use uhc\Loader;

class Scenario implements Listener
{
	/** @var string */
	private $name;
	/** @var Loader */
	protected $plugin;
	/** @var bool */
	private $activeScenario = false;

	public function __construct(Loader $plugin, string $name)
	{
		$this->plugin = $plugin;
		$this->name = $name;
	}

	public function getName(): string
	{
		return $this->name;
	}

	public final function setActive(bool $active): void
	{
		$this->activeScenario = $active;
		if ($active) {
			$this->plugin->getServer()->getPluginManager()->registerEvents($this, $this->plugin);
		} else {
			HandlerListManager::global()->unregisterAll($this);
		}
	}

	public final function isActive(): bool
	{
		return $this->activeScenario;
	}
}
