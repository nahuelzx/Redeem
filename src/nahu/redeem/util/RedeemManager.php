<?php

declare(strict_types=1);

namespace nahu\redeem\util;

use nahu\redeem\Main;
use muqsit\invmenu\InvMenu;
use muqsit\invmenu\transaction\InvMenuTransaction;
use muqsit\invmenu\transaction\InvMenuTransactionResult;
use pocketmine\inventory\Inventory;
use pocketmine\item\Item;
use pocketmine\player\Player;
use pocketmine\utils\Config;
use pocketmine\utils\TextFormat as TF;

class RedeemManager {

    private Config $redeemData;
    private Config $redeemClaims;
    private Main $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;

        $plugin->saveResource("redeems.yml");
        $plugin->saveResource("claims.yml");

        $this->redeemData = new Config($plugin->getDataFolder() . "redeems.yml", Config::YAML);
        $this->redeemClaims = new Config($plugin->getDataFolder() . "claims.yml", Config::YAML);
    }

    public function redeemExists(string $name): bool {
        return $this->redeemData->exists($name);
    }

    public function hasClaimed(Player $player, string $name): bool {
        $claimed = $this->redeemClaims->getNested($player->getName(), false);
        return $claimed === true;
    }

    public function openRedeemEditor(Player $player, string $name, bool $isNew): void {
        if ($isNew && $this->redeemExists($name)) {
            $player->sendMessage(TF::RED . 'This redeem already exists.');
            return;
        }

        $menu = InvMenu::create(InvMenu::TYPE_HOPPER);
        $menu->setName(
            ($isNew ? TF::LIGHT_PURPLE . "Create loot: " : TF::LIGHT_PURPLE . "Edit loot: ") .
            TF::GRAY . $name
        );

        if (!$isNew && $this->redeemExists($name)) {
            $stored = $this->redeemData->getNested($name, []);
            $menu->getInventory()->setContents($this->deserializeItems($stored));
        }

        $menu->setListener(function(InvMenuTransaction $transaction): InvMenuTransactionResult {
            return $transaction->continue();
        });

        $menu->setInventoryCloseListener(function(Player $viewer, Inventory $inventory) use ($name, $player): void {
            if (!$viewer->getUniqueId()->equals($player->getUniqueId())) {
                return;
            }

            $contents = $inventory->getContents();
            $serialized = $this->serializeItems($contents);

            if (empty($serialized)) {
                if ($this->redeemData->exists($name)) {
                    $this->redeemData->remove($name);
                    $this->redeemData->save();
                }
                $viewer->sendMessage(
                    TF::YELLOW . "Redeem is Empty. " . TF::GRAY . "Nothing saved."
                );
                return;
            }

            $this->redeemData->setNested($name, $serialized);
            $this->redeemData->save();

            $viewer->sendMessage(
                TF::LIGHT_PURPLE . "Redeem saved: " . TF::GRAY . $name
            );
        });

        $menu->send($player);
    }

    public function claimRedeem(Player $player, string $name): void {
        if (!$this->redeemExists($name)) {
            $player->sendMessage(TF::RED . "Redeem not found.");
            return;
        }

        if ($this->hasClaimed($player, $name)) {
            $player->sendMessage(
                TF::YELLOW . "You already claimed this redeem."
            );
            return;
        }

        $itemsData = $this->redeemData->getNested($name, []);
        $items = $this->deserializeItems($itemsData);

        if (empty($items)) {
            $player->sendMessage(TF::RED . "This redeem has no items.");
            return;
        }

        foreach ($items as $item) {
            if (!$player->getInventory()->canAddItem($item)) {
                $player->sendMessage(TF::RED . "Your inventory is full.");
                return;
            }
        }

        foreach ($items as $item) {
            $player->getInventory()->addItem($item);
        }

        $this->redeemClaims->setNested($player->getName(), true);
        $this->redeemClaims->save();

        $player->sendMessage(
            TF::LIGHT_PURPLE . "Redeem claimed: " . TF::GRAY . $name
        );
    }
    
    public function serializeItems(array $items): array {
        return array_map(fn(Item $item) => Serialize::serialize($item), array_filter($items, fn($item) => $item instanceof Item && !$item->isNull()));
    }

    public function deserializeItems(array $items): array {
        return array_map(fn($data) => $this->deserializeItem($data), $items);
    }

    public function deserializeItem(string $itemData): Item {
        $item = Serialize::deserialize($itemData);
        return $item ?? Item::null();
    }
}