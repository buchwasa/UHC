<?php

declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\utils\TextFormat;
use uhc\game\type\GameStatus;
use uhc\Loader;

class UHCCommand extends PluginCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("uhc", $plugin);
        $this->plugin = $plugin;
        $this->setPermission("uhc.command.uhc");
        $this->setUsage("/uhc");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args)
    {
        if (!$this->testPermission($sender)) {
            return;
        }

        if ($this->plugin->getHeartbeat()->hasStarted()) {
            $sender->sendMessage(TextFormat::RED . "UHC already started!");

            return;
        } else {
            $this->plugin->getHeartbeat()->setGameStatus(GameStatus::COUNTDOWN);
            $sender->sendMessage(TextFormat::GREEN . "The UHC has been started successfully!");
            Command::broadcastCommandMessage($sender, "Started the UHC", false);
        }

        return;
    }
}
