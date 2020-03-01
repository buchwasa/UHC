<?php
declare(strict_types=1);

namespace uhc\form;

use pocketmine\form\Form;
use pocketmine\Player;

class CustomForm implements Form{
	/** @var array */
	private $json = [];
	/** @var array */
	private $content = [];

	public function __construct(string $title){
		$this->json = [
			"type" => "custom_form",
			"title" => $title,
			"content" => []
		];
	}

	public function addDropdown(string $text, array $options, callable $callable) : void{
		$this->json["content"][] = [
			"type" => "dropdown",
			"text" => $text,
			"options" => $options,
			"default" => 0
		];

		$this->content[] = $callable;
	}

	public function addToggle(string $text, bool $defaultEnabled, callable $callable) : void{
		$this->json["content"][] = [
			"type" => "toggle",
			"text" => $text,
			"default" => $defaultEnabled
		];

		$this->content[] = $callable;
	}


	/**
	 * @inheritDoc
	 */
	public function handleResponse(Player $player, $data) : void{
		if($this->json !== null){
			foreach((array)$data as $index => $value){
				$this->content[$index]($player, $value);
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