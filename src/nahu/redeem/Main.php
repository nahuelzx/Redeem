<?php

declare(strict_types=1);

namespace nahu\redeem;

use nahu\redeem\command\RedeemCommand;
use nahu\redeem\util\RedeemManager;
use muqsit\invmenu\InvMenuHandler;
use pocketmine\plugin\PluginBase;

class Main extends PluginBase {

    private RedeemManager $redeemManager;

    protected function onEnable(): void {
        if (!InvMenuHandler::isRegistered()) {
            InvMenuHandler::register($this);
        }

        @mkdir($this->getDataFolder());

        $this->redeemManager = new RedeemManager($this);

        $this->getServer()->getCommandMap()->register(
            "redeem",
            new RedeemCommand($this->redeemManager)
        );
    }

    public function getRedeemManager(): RedeemManager {
        return $this->redeemManager;
    }
}