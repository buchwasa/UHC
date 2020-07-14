<?php

declare(strict_types=1);

namespace uhc\game;

use pocketmine\block\VanillaBlocks;
use pocketmine\math\Vector3;
use pocketmine\player\Player;
use pocketmine\world\World;
use muqsit\chunkloader\ChunkRegion;
use function mt_rand;

class Border
{
	/** @var int */
	private $size = 1000;
	/** @var World */
	private $world;
	/** @var int */
	private $safeX;
	/** @var int */
	private $safeZ;

	public function __construct(World $world)
	{
		$this->world = $world;
		$this->safeX = $world->getSafeSpawn()->getFloorX();
		$this->safeZ = $world->getSafeSpawn()->getFloorZ();
	}

	public function setSize(int $size): void
	{
		$this->size = $size;
	}

	public function getSize(): int
	{
		return $this->size;
	}

	public function getX(bool $isNegative = false): int
	{
		return $isNegative ? ($this->safeX - $this->size) : ($this->safeX + $this->size);
	}

	public function getZ(bool $isNegative = false): int
	{
		return $isNegative ? ($this->safeZ - $this->size) : ($this->safeZ + $this->size);
	}

	public function isPlayerInsideOfBorder(Player $p): bool
	{
		$playerPos = $p->getPosition();
		if (
			$playerPos->getFloorX() > $this->getX() || $playerPos->getFloorX() < $this->getX(true) ||
			$playerPos->getFloorZ() > $this->getZ() || $playerPos->getFloorZ() < $this->getZ(true)
		) {
			return false;
		}

		return true;
	}

	public function teleportPlayer(Player $p): void
	{
		$x = mt_rand(5, 20);
		$z = mt_rand(5, 20);
		if ($p->getX() < 0 && $p->getZ() < 0) {
			$pX = $this->getX(true) + $x;
			$pZ = $this->getZ(true) + $z;
		} elseif ($p->getX() > 0 && $p->getZ() > 0) {
			$pX = $this->getX() - $x;
			$pZ = $this->getZ() - $z;
		} elseif ($p->getX() < 0 && $p->getZ() > 0) {
			$pX = $this->getX(true) + $x;
			$pZ = $this->getZ() - $z;
		} else {
			$pX = $this->getX() - $x;
			$pZ = $this->getZ(true) + $z;
		}

		ChunkRegion::onChunkGenerated($this->world, $pX >> 4, $pZ >> 4, function () use ($p, $pX, $pZ): void {
			$p->teleport(new Vector3($pX, $this->world->getHighestBlockAt($pX, $pZ) + 1, $pZ));
		});
	}

	public function build(): void
	{ //TODO: Run this in a closure task.
		for ($minX = $this->getX(true); $minX <= $this->getX(); $minX++) {
			ChunkRegion::onChunkGenerated($this->world, $minX >> 4, $this->getZ() >> 4, function () use ($minX): void {
				$highestBlock = $this->world->getHighestBlockAt($minX, $this->getZ());
				for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
					$this->world->setBlock(new Vector3($minX, $y, $this->getZ()), VanillaBlocks::BEDROCK());
				}

				$highestBlock = $this->world->getHighestBlockAt($minX, $this->getZ(true));
				for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
					$this->world->setBlock(new Vector3($minX, $y, $this->getZ(true)), VanillaBlocks::BEDROCK());
				}
			});
		}

		for ($minZ = $this->getZ(true); $minZ <= $this->getZ(); $minZ++) {
			ChunkRegion::onChunkGenerated($this->world, $this->getX() >> 4, $minZ >> 4, function () use ($minZ): void {
				$highestBlock = $this->world->getHighestBlockAt($this->getX(), $minZ);
				for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
					$this->world->setBlock(new Vector3($this->getX(), $y, $minZ), VanillaBlocks::BEDROCK());
				}

				$highestBlock = $this->world->getHighestBlockAt($this->getX(true), $minZ);
				for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
					$this->world->setBlock(new Vector3($this->getX(true), $y, $minZ), VanillaBlocks::BEDROCK());
				}
			});
		}
	}
}
