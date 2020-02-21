<?php
declare(strict_types=1);

namespace uhc\utils;

use pocketmine\level\ChunkLoader;
use pocketmine\level\format\Chunk;
use pocketmine\level\Level;
use pocketmine\level\Position;
use pocketmine\math\Vector3;

class NetworkChunkLoader implements ChunkLoader{
	/** @var Position */
	private $position;
	/** @var int */
	private $x;
	/** @var int */
	private $z;
	/** @var int */
	private $loaderId = 0;
	/** @var callable */
	private $callback;

	public function __construct(Level $level, int $chunkX, int $chunkZ, callable $callback){
		$this->position = Position::fromObject(new Vector3($chunkX << 4, $chunkZ << 4), $level);
		$this->x = $chunkX;
		$this->z = $chunkZ;
		$this->loaderId = Level::generateChunkLoaderId($this);
		$this->callback = $callback;
	}

	public function onChunkLoaded(Chunk $chunk){
		if(!$chunk->isPopulated()){
			$this->getLevel()->populateChunk((int) $this->getX(), (int) $this->getZ());
			return;
		}
		$this->onComplete();
	}

	public function onChunkPopulated(Chunk $chunk){
		$this->onComplete();
	}

	public function onComplete() : void{
		$this->getLevel()->unregisterChunkLoader($this, (int) $this->getX(), (int) $this->getZ());
		($this->callback)();
	}

	public function getLoaderId() : int{
		return $this->loaderId;
	}

	public function isLoaderActive() : bool{
		return true;
	}

	public function getPosition(){
		return $this->position;
	}

	public function getLevel(){
		return $this->position->getLevel();
	}

	public function getX(){
		return $this->x;
	}

	public function getZ(){
		return $this->z;
	}

	public function onChunkChanged(Chunk $chunk){
	}

	public function onChunkUnloaded(Chunk $chunk){
	}

	public function onBlockChanged(Vector3 $block){
	}
}