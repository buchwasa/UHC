<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use uhc\Loader;

class TeamCommand extends BaseCommand
{
	/** @var Loader */
	private $plugin;

	public function __construct(Loader $plugin)
	{
		parent::__construct("team", "Team management", "/team <add:create:disband:leave> <player:team>");
		$this->plugin = $plugin;
	}

	public function onExecute(Player $sender, array $args): void
	{
		$session = $this->plugin->getSessionManager()->getSession($sender);
		switch (strtolower($args[0])) {
			case "create":
				if (count($args) < 2) {
					throw new InvalidCommandSyntaxException();
				} elseif ($session->isInTeam()) {
					$sender->sendMessage("You are already in a team!");
					return;
				}
				$this->plugin->getTeamManager()->createTeam($args[1], $sender);
				$this->plugin->getSessionManager()->getSession($sender)->addToTeam($this->plugin->getTeamManager()->getTeam($args[1]));
				$sender->sendMessage("Successfully created your team!");
				break;
			case "disband":
				if (!isset($args[0])) {
					throw new InvalidCommandSyntaxException();
				} elseif (!$session->isInTeam()) {
					$sender->sendMessage("You must be in a team to disband it!");
					return;
				} elseif (!$session->isTeamLeader()) {
					$sender->sendMessage("You must be team leader to disband your team!");
					return;
				}
				$teamName = $session->getTeam()->getName();
				foreach ($session->getTeam()->getMembers() as $member) {
					$this->plugin->getSessionManager()->getSession($member)->removeFromTeam();
				}
				$this->plugin->getTeamManager()->disbandTeam($teamName);
				$sender->sendMessage("Successfully disbanded your team!");
				break;
			case "add":
				if (count($args) < 2) {
					throw new InvalidCommandSyntaxException();
				} else if (!$session->isInTeam()) {
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

				$this->plugin->getSessionManager()->getSession($addedPlayer)->addToTeam($session->getTeam());
				$sender->sendMessage("Successfully added {$addedPlayer->getDisplayName()} to your team!");
				break;
			case "leave":
				if (!isset($args[0])) {
					throw new InvalidCommandSyntaxException();
				} elseif (!$session->isInTeam()) {
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
