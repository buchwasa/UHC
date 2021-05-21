<?php
declare(strict_types=1);

namespace uhc\game\type;

//TODO: Make configurable
final class GameTimer
{

	/** @var int */
	public const TIMER_COUNTDOWN = 30;
	/** @var int */
	public const TIMER_GRACE = 60 * 20;
	/** @var int */
	public const TIMER_PVP = 60 * 30;
	/** @var int */
	public const TIMER_NORMAL = 60 * 60;

}