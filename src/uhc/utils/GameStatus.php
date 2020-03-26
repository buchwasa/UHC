<?php

declare(strict_types=1);

namespace uhc\utils;

interface GameStatus{
	/** @var int */
	public const WAITING = -1;
	/** @var int */
	public const COUNTDOWN = 0;
	/** @var int */
	public const GRACE = 1;
	/** @var int */
	public const PVP = 2;
	/** @var int */
	public const NORMAL = 3;
}
