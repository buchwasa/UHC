<?php

declare(strict_types=1);

namespace uhc\utils;

use pocketmine\network\mcpe\protocol\RemoveObjectivePacket;
use pocketmine\network\mcpe\protocol\SetDisplayObjectivePacket;
use pocketmine\network\mcpe\protocol\SetScorePacket;
use pocketmine\network\mcpe\protocol\types\ScorePacketEntry;
use pocketmine\Player;
use function str_repeat;

class Scoreboard{

	/** @var array */
	protected static $scoreboards = [];

	public static function setTitle(Player $player, string $title) : void{
		if(isset(self::$scoreboards[$player->getName()])){
			self::removeScoreboard($player);
		}
		$pk = new SetDisplayObjectivePacket();
		$pk->displaySlot = "sidebar";
		$pk->objectiveName = "objective";
		$pk->displayName = $title;
		$pk->criteriaName = "dummy";
		$pk->sortOrder = 0;
		$player->sendDataPacket($pk);

		self::$scoreboards[$player->getName()] = "objective";
	}

	public static function clear(Player $player) : void{
		for($line = 0; $line <= 15; $line++){
			self::removeLine($player, $line);
		}
	}

	public static function removeScoreboard(Player $player) : void{
		$pk = new RemoveObjectivePacket();
		$pk->objectiveName = "objective";
		$player->sendDataPacket($pk);

		unset(self::$scoreboards[$player->getName()]);
	}

	public static function removeLine(Player $player, int $line) : void{
		$pk = new SetScorePacket();
		$pk->type = SetScorePacket::TYPE_REMOVE;
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "objective";
		$entry->score = 15 - $line;
		$entry->scoreboardId = ($line);
		$pk->entries[] = $entry;
		$player->sendDataPacket($pk);
	}

	public static function setLine(Player $player, int $score, string $line) : void{
		$entry = new ScorePacketEntry();
		$entry->objectiveName = "objective";
		$entry->type = $entry::TYPE_FAKE_PLAYER;
		$entry->customName = $line;
		$entry->score = $score;
		$entry->scoreboardId = $score;
		$entry->entityUniqueId = $player->getId();

		$pk = new SetScorePacket();
		$pk->type = $pk::TYPE_CHANGE;
		$pk->entries[] = $entry;
		$player->sendDataPacket($pk);
	}

	public static function setEmptyLine(Player $player, int $line) : void{
		$text = str_repeat(" ", $line);
		self::setLine($player, $line, $text);
	}
}