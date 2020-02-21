<?php

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use uhc\form\SimpleForm;
use uhc\Loader;
use uhc\tasks\UHCTimer;

class UHCCommand extends PluginCommand{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin){
		parent::__construct("uhc", $plugin);
		$this->plugin = $plugin;
		$this->setPermission("uhc.command.uhc");
		$this->setUsage("/uhc");
	}


	public function execute(CommandSender $sender, string $commandLabel, array $args) : bool{
		if(!$this->testPermission($sender)){
			return true;
		}

		$form = new SimpleForm("UHC");
		$form->addButton("Start UHC", function(Player $player, $data){
			if($data === 0){
				if(UHCTimer::$gameStatus >= UHCTimer::STATUS_COUNTDOWN){
					$player->sendMessage(TextFormat::RED . "UHC already started!");
				}
				UHCTimer::$gameStatus = UHCTimer::STATUS_COUNTDOWN;
			}
		});

		$form->addButton("Teleport All", function(Player $player, $data){
			if($data === 1){
				foreach($this->plugin->getServer()->getOnlinePlayers() as $p){
					$p->teleport($player->getPosition());
				}
			}
		});

		$form->addButton("GlobalMute", function(Player $player, $data){
			if($data === 2){
				if(!$this->plugin->globalMute){
					$this->plugin->globalMute = true;
					$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been disabled by an admin!");
				}else{
					$this->plugin->globalMute = false;
					$this->plugin->getServer()->broadcastMessage(TextFormat::GREEN . "Chat has been enabled by an admin!");
				}
			}
		});

		$form->addButton("Post to Discord", function(Player $player, $data){
			if($data === 3){
				$scenarios = [];
				foreach($this->plugin->getScenarios() as $scenario){
					if($scenario->isActive()){
						$scenarios[] = $scenario->getName();
					}
				}
				$webhook = curl_init();
				curl_setopt($webhook, CURLOPT_URL, "https://discordapp.com/api/webhooks/679058488760205347/jDOmtI06dlKH6lh9F8EJXqmHJxmfDri4buDbeP2dBCYqXYjiJd4KTnGdG4YO5Fk6a5V3");
				curl_setopt($webhook, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
				curl_setopt($webhook, CURLOPT_POST, true);
				curl_setopt($webhook, CURLOPT_POSTFIELDS, json_encode(
					[
						"content" => "<@&675893937755521024>",
						"embeds" => [
							[
								"title" => "To2 UHC Starting Soon!",
								"type" => "rich",
								"timestamp" => date(DATE_ISO8601, time()),
								"color" => hexdec("6C9AD8"),
								"fields" => [
									[
										"name" => "Scenarios",
										"value" => ($scenarios === [] ? "None" : implode(", ", $scenarios)),
										"inline" => true
									]
								],
								"footer" => [
									"text" => "IP: paragon.wumpotam.us"
								]
							]
						]
					], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
				));
				curl_setopt($webhook, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($webhook, CURLOPT_SSL_VERIFYHOST, false);
				curl_setopt($webhook, CURLOPT_SSL_VERIFYPEER, false);
				curl_exec($webhook);
			}
		});

		//TODO: Heal

		$sender->sendForm($form);

		return true;
	}
}