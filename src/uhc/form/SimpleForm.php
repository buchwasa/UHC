<?php

declare(strict_types=1);

namespace uhc\form;

use pocketmine\form\Form;
use pocketmine\Player;

class SimpleForm implements Form{
	/** @var array */
	private $json = [];
	/** @var array */
	private $buttons = [];

	public function __construct(string $title, string $content = ""){
		$this->json = [
			"type" => "form",
			"title" => $title,
			"content" => $content,
			"buttons" => []
		];
	}

	public function addButton(string $text, callable $callable = null, string $imageUrl = "") : void{
		$button = ["text" => $text];
		if($imageUrl !== ""){
			$button["image"]["type"] = "url";
			$button["image"]["data"] = $imageUrl;
		}

		$this->json["buttons"][] = $button;
		$this->buttons[] = $callable ?? function(){

			};
	}

	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data) : void{
		if($this->json !== null){
			if($data !== null){
				$this->buttons[(int) $data]($player, (int) $data);
			}
		}
	}
	/**
	 * @inheritDoc
	 */
	public function jsonSerialize() : array{
		return $this->json;
	}
}