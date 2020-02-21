<?php
declare(strict_types=1);

namespace uhc;

use pocketmine\entity\Effect;
use pocketmine\entity\EffectInstance;
use pocketmine\item\GoldenApple;
use pocketmine\utils\TextFormat as TF;

class GoldenHead extends GoldenApple{

	public function __construct($meta = 0){
		parent::__construct($meta);
		$meta == 1 ? $this->setCustomName(TF::RESET . TF::GOLD . "Golden Head") : $this->setCustomName(TF::RESET . "Golden Apple");
	}

	public function getAdditionalEffects() : array{
		return [
			new EffectInstance(Effect::getEffect(Effect::REGENERATION), 20 * ($this->getDamage() == 1 ? 10 : 5), 1, false),
			new EffectInstance(Effect::getEffect(Effect::ABSORPTION), 20 * 120, 0, false),
		];
	}

}