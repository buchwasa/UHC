<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\Player;
use pocketmine\utils\TextFormat;
use uhc\Loader;
use function mb_strtolower;

class SpectatorCommand extends BaseCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("spectate", $plugin);
        $this->plugin = $plugin;
        $this->setUsage("/spectate <playerName>");
    }

    public function onExecute(Player $sender, array $args): void
    {
        if ($sender->getGamemode() !== 3) {
            $sender->sendMessage(TextFormat::RED . "You must be in spectator mode to use this command!");

            return;
        }

        if (!isset($args[0])) {
            throw new InvalidCommandSyntaxException();
        }

        $player = $this->plugin->getServer()->getPlayer(mb_strtolower($args[0]));
        if ($player === null) {
            $sender->sendMessage(TextFormat::RED . "That player is offline!");

            return;
        }

        if ($player === $sender) {
            $sender->sendMessage(TextFormat::RED . "You can't spectate yourself!");

            return;
        } else {
            $sender->teleport($player->getPosition());
            $sender->sendMessage(TextFormat::GREEN . "Now spectating: " . $player->getDisplayName());

            return;
        }
    }
}
