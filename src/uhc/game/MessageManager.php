<?php

declare(strict_types=1);

namespace uhc\game;

use uhc\UHC;

class MessageManager
{
	/** @var UHC */
	private UHC $plugin;

	public function __construct(UHC $plugin)
	{
		$this->plugin = $plugin;
	}

	/**
	 * @return UHC
	 */
	public function getPlugin(): UHC
	{
		return $this->plugin;
	}

	public function getDeathMessages(): array
	{
		return $this->getPlugin()->config->get("death-messages");
	}

}