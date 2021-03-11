<?php
declare(strict_types=1);

namespace uhc\command;

use pocketmine\command\utils\InvalidCommandSyntaxException;
use pocketmine\player\Player;
use uhc\UHC;

class TeamCommand extends BaseCommand
{

	public function __construct(UHC $plugin)
	{
		parent::__construct($plugin, "team", "Team management", "/team <add:create:disband:leave> <player>");
	}

	public function onExecute(Player $sender, array $args): void
	{
		$session = $this->getPlugin()->getSessionManager()->getSession($sender);
		if(!isset($args[0])){
			throw new InvalidCommandSyntaxException();
		}
		switch (strtolower($args[0])) { //team notifications really needed.
			case "create":
				if ($session->isInTeam()) {
					$sender->sendMessage("You are already in a team!");
					return;
				}
				$this->getPlugin()->getSessionManager()->getSession($sender)->addToTeam($this->getPlugin()->getTeamManager()->createTeam($sender));
				$sender->sendMessage("Successfully created your team!");
				break;
			case "disband": //Doesn't actually disband
				if (!$session->isInTeam()) {
					$sender->sendMessage("You must be in a team to disband it!");
					return;
				} elseif (!$session->isTeamLeader()) {
					$sender->sendMessage("You must be team leader to disband your team!");
					return;
				}
				$teamNumber = $session->getTeam()->getNumber();
				foreach ($session->getTeam()->getMembers() as $member) {
					$this->getPlugin()->getSessionManager()->getSession($member)->removeFromTeam();
				}
				$this->getPlugin()->getTeamManager()->disbandTeam($teamNumber);
				$sender->sendMessage("Successfully disbanded your team!");
				break;
			case "add": //Check if team is full, team leaders aren't actually leaders after relogging?
				if (!isset($args[1])) {
					throw new InvalidCommandSyntaxException();
				} else if (!$session->isInTeam()) {
					$sender->sendMessage("You must be in a team to add players!");
					return;
				} elseif (!$session->isTeamLeader()) {
					$sender->sendMessage("You must be team leader to add players!");
					return;
				}
				$addedPlayer = $this->getPlugin()->getServer()->getPlayer($args[1]);
				if (!$addedPlayer instanceof Player) {
					$sender->sendMessage("You must add a valid player!");
					return;
				}

				$this->getPlugin()->getSessionManager()->getSession($addedPlayer)->addToTeam($session->getTeam());
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
			default:
				throw new InvalidCommandSyntaxException();
		}
	}
}
