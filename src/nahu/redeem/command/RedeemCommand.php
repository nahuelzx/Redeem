<?php

declare(strict_types=1);

namespace nahu\redeem\command;

use nahu\redeem\util\RedeemManager;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat as TF;

class RedeemCommand extends Command {

    private RedeemManager $manager;

    public function __construct(RedeemManager $manager) {
        parent::__construct(
            "redeem",
            "For HCF.",
            "/redeem <create|edit|claim> <name>",
            []
        );
        $this->manager = $manager;
        $this->setPermission("redeem.command");
    }

    public function execute(CommandSender $sender, string $label, array $args): void {
        if (!$sender instanceof Player) {
            $sender->sendMessage(TF::RED . "This command can only be used in-game.");
            return;
        }

        if (!$this->testPermission($sender)) {
            $sender->sendMessage(TF::RED . "You don't have permission to use this command.");
            return;
        }

        if (count($args) < 2) {
            $this->sendUsage($sender);
            return;
        }

        $subCommand = strtolower($args[0]);
        $name = $args[1];

        if (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $name)) {
            $sender->sendMessage(TF::RED . "You must use between 3 and 20 characters.");
            return;
        }

        switch ($subCommand) {
            case "create":
                $this->handleCreate($sender, $name);
                break;
            case "edit":
                $this->handleEdit($sender, $name);
                break;
            case "claim":
                $this->handleClaim($sender, $name);
                break;
            default:
                $this->sendUsage($sender);
                break;
        }
    }

    private function handleCreate(Player $player, string $name): void {
        if (!$player->hasPermission("redeem.create")) {
            $player->sendMessage(TF::RED . "You don't have permission to use this command.");
            return;
        }

        if ($this->manager->redeemExists($name)) {
            $player->sendMessage(TF::RED . "The name is already in use.");
            return;
        }

        $this->manager->openRedeemEditor($player, $name, true);
        $player->sendMessage(
            TF::LIGHT_PURPLE . "Creating redeem " .
            TF::GRAY . $name .
            TF::LIGHT_PURPLE . ". Put the items in the hopper and close it to save."
        );
    }

    private function handleEdit(Player $player, string $name): void {
        if (!$player->hasPermission("redeem.edit")) {
            $player->sendMessage(TF::RED . "You don't have permission to use this command.");
            return;
        }

        if (!$this->manager->redeemExists($name)) {
            $player->sendMessage(TF::RED . "This redeem does not exist.");
            return;
        }

        $this->manager->openRedeemEditor($player, $name, false);
        $player->sendMessage(
            TF::LIGHT_PURPLE . "Editing redeem " .
            TF::GRAY . $name .
            TF::LIGHT_PURPLE . ". Modify the items and close to save."
        );
    }

    private function handleClaim(Player $player, string $name): void {
        if (!$this->manager->redeemExists($name)) {
            $player->sendMessage(TF::RED . "There is no redeem with that name.");
            return;
        }

        if ($this->manager->hasClaimed($player, $name)) {
            $player->sendMessage(
                TF::YELLOW . "You already claimed a redeem. "
            );
            return;
        }

        $this->manager->claimRedeem($player, $name);
    }

    private function sendUsage(Player $player): void {
        $player->sendMessage(TF::GRAY . "Usage:");
        $player->sendMessage(TF::GRAY . "/redeem create <name> - Create a new redeem");
        $player->sendMessage(TF::GRAY . "/redeem edit <name> - Edit an existing redeem");
        $player->sendMessage(TF::GRAY . "/redeem claim <name> - Claim a redeem");
    }
}
