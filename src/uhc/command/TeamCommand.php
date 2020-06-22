<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\CommandSender;
use pocketmine\command\PluginCommand;
use pocketmine\Player;
use uhc\Loader;

class TeamCommand extends BaseCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("team", $plugin);
        $this->plugin = $plugin;
        $this->setUsage("/team [create:disband:add:leave] <teamName:playerName>");
    }

    public function onExecute(Player $sender, array $args): void
    {
        $session = $this->plugin->getSession($sender);
        switch (strtolower($args[0])) {
            case "create":
                if ($session->isInTeam()) {
                    $sender->sendMessage("You are already in a team!");
                    return;
                }
                $this->plugin->addTeam($args[1], $sender);
                $sender->sendMessage("Successfully created your team!");
                break;
            case "disband":
                if (!$session->isInTeam()) {
                    $sender->sendMessage("You must be in a team to disband it!");
                    return;
                } elseif (!$session->isTeamLeader()) {
                    $sender->sendMessage("You must be team leader to disband your team!");
                    return;
                }
                $teamName = $session->getTeam()->getName();
                foreach ($session->getTeam()->getMembers() as $member) {
                    $this->plugin->getSession($member)->removeFromTeam();
                }
                $this->plugin->removeTeam($teamName);
                $sender->sendMessage("Successfully disbanded your team!");
                break;
            case "add":
                if (!$session->isInTeam()) {
                    $sender->sendMessage("You must be in a team to add players!");
                    return;
                } elseif (!$session->isTeamLeader()) {
                    $sender->sendMessage("You must be team leader to add players!");
                    return;
                }
                $addedPlayer = $this->plugin->getServer()->getPlayer($args[1]);
                if (!$addedPlayer instanceof Player) {
                    $sender->sendMessage("You must add a valid player!");
                    return;
                }

                $this->plugin->getSession($addedPlayer)->addToTeam($session->getTeam());
                $sender->sendMessage("Successfully added {$addedPlayer->getDisplayName()} to your team!");
                break;
            case "leave":
                if (!$session->isInTeam()) {
                    $sender->sendMessage("You must be in a team to leave!");
                    return;
                } elseif ($session->isTeamLeader()) {
                    $sender->sendMessage("Use /team disband to disband your team!");
                    return;
                }

                $session->removeFromTeam();
                $sender->sendMessage("Successfully left your team!");
                break;
        }
    }
}
