<?php
declare(strict_types=1);

namespace uhc\form;

use pocketmine\form\Form;
use pocketmine\Player;

class SimpleForm implements Form{

	private $json = [];
	private $buttons = [];
	private $responseForm;

	public function __construct(string $title, string $content = ""){
		$this->json = [
			"type" => "form",
			"title" => $title,
			"content" => $content,
			"buttons" => []
		];
	}

	public function addButton(string $text, callable $callable = null, string $imageUrl = ""){
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

		if($this->responseForm !== null){
			$player->sendForm($this->responseForm);
		}
	}
	/**
	 * @inheritDoc
	 */
	public function jsonSerialize(){
		return $this->json;
	}
}