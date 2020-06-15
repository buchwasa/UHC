<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use uhc\Loader;

class TpallCommand extends BaseCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("tpall", $plugin);
        $this->plugin = $plugin;
        $this->setPermission("uhc.command.tpall");
    }

    public function onExecute(Player $sender, array $args): void
    {
        foreach ($this->plugin->getServer()->getOnlinePlayers() as $p) {
            $p->teleport($sender->getPosition());
        }
        Command::broadcastCommandMessage($sender, "Teleported everyone");
    }
}