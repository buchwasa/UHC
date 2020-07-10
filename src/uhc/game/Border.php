<?php

declare(strict_types=1);

namespace uhc\game;

use pocketmine\block\BlockFactory;
use pocketmine\block\BlockIds;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use muqsit\chunkloader\ChunkRegion;
use function mt_rand;

class Border
{
    /** @var int */
    private $size = 1000;
    /** @var Level */
    private $level;
    /** @var int */
    private $safeX;
    /** @var int */
    private $safeZ;

    public function __construct(Level $level)
    {
        $this->level = $level;
        $this->safeX = $level->getSafeSpawn()->getFloorX();
        $this->safeZ = $level->getSafeSpawn()->getFloorZ();
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
        if (
            $p->getFloorX() > $this->getX() || $p->getFloorX() < $this->getX(true) ||
            $p->getFloorZ() > $this->getZ() || $p->getFloorZ() < $this->getZ(true)
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

        ChunkRegion::onChunkGenerated($this->level, $pX >> 4, $pZ >> 4, function () use ($p, $pX, $pZ): void {
            $p->teleport(new Vector3($pX, $this->level->getHighestBlockAt($pX, $pZ) + 1, $pZ));
        });
    }

    public function build(): void
    { //TODO: Run this in a closure task.
        for ($minX = $this->getX(true); $minX <= $this->getX(); $minX++) {
            ChunkRegion::onChunkGenerated($this->level, $minX >> 4, $this->getZ() >> 4, function () use ($minX): void {
                $highestBlock = $this->level->getHighestBlockAt($minX, $this->getZ());
                for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
                    $this->level->setBlock(new Vector3($minX, $y, $this->getZ()), BlockFactory::get(BlockIds::BEDROCK));
                }

                $highestBlock = $this->level->getHighestBlockAt($minX, $this->getZ(true));
                for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
                    $this->level->setBlock(new Vector3($minX, $y, $this->getZ(true)), BlockFactory::get(BlockIds::BEDROCK));
                }
            });
        }

        for ($minZ = $this->getZ(true); $minZ <= $this->getZ(); $minZ++) {
            ChunkRegion::onChunkGenerated($this->level, $this->getX() >> 4, $minZ >> 4, function () use ($minZ): void {
                $highestBlock = $this->level->getHighestBlockAt($this->getX(), $minZ);
                for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
                    $this->level->setBlock(new Vector3($this->getX(), $y, $minZ), BlockFactory::get(BlockIds::BEDROCK));
                }

                $highestBlock = $this->level->getHighestBlockAt($this->getX(true), $minZ);
                for ($y = $highestBlock; $y <= $highestBlock + 4; $y++) {
                    $this->level->setBlock(new Vector3($this->getX(true), $y, $minZ), BlockFactory::get(BlockIds::BEDROCK));
                }
            });
        }
    }
}
