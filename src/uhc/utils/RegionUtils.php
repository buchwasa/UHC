<?php

declare(strict_types=1);

namespace uhc\utils;

use pocketmine\level\Level;

final class RegionUtils{
	
	public static function onChunkGenerated(Level $level, int $chunkX, int $chunkZ, callable $callback) : void{
		if($level->isChunkPopulated($chunkX, $chunkZ)){
			$callback();
			return;
		}
		$level->registerChunkLoader(new NetworkChunkLoader($level, $chunkX, $chunkZ, $callback), $chunkX, $chunkZ, true);
	}
}