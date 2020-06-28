<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;
use uhc\Loader;
use function mb_strtolower;

class HealCommand extends BaseCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("heal", $plugin);
        $this->plugin = $plugin;
        $this->setUsage("/heal <playerName>");
        $this->setPermission("uhc.command.heal");
    }

    public function onExecute(Player $sender, array $args): void
    {
        if (!isset($args[0])) {
            throw new InvalidCommandSyntaxException();
        }

        $player = $this->plugin->getServer()->getPlayer(mb_strtolower($args[0]));
        if ($player !== null) {
            $player->setHealth($player->getMaxHealth());
            $player->getHungerManager()->setFood($player->getHungerManager()->getMaxFood());
            $sender->sendMessage(TF::RED . "You have healed " . TF::BOLD . TF::AQUA . $player->getDisplayName() . TF::RESET . TF::RED . "!");
            Command::broadcastCommandMessage($sender, "Healed: " . $player->getDisplayName(), false);
        }

        return;
    }
}
