<?php

declare(strict_types=1);

namespace uhc\command;

use dktapps\pmforms\CustomForm;
use dktapps\pmforms\CustomFormResponse;
use dktapps\pmforms\element\Toggle;
use pocketmine\Player;
use uhc\Loader;

class ScenariosCommand extends BaseCommand
{
    /** @var Loader */
    private $plugin;

    public function __construct(Loader $plugin)
    {
        parent::__construct("scenario", $plugin);
        $this->plugin = $plugin;
        $this->setAliases(["sc"]);
        $this->setUsage("/scenario");
    }

    public function onExecute(Player $sender, array $args): void
    {
        $toggles = [];
        foreach ($this->plugin->getScenarios() as $scenario) {
            $toggles[] = new Toggle($scenario->getName(), $scenario->getName(), $scenario->isActive());
        }

        $form = new CustomForm("Scenarios", $toggles, function (Player $player, CustomFormResponse $response): void {
            foreach ($this->plugin->getScenarios() as $scenario) {
                if (!$player->hasPermission("uhc.scenarios.enable")) {
                    return;
                }
                $scenario->setActive($response->getBool($scenario->getName()));
            }
        });

        $sender->sendForm($form);

        return;
    }
}