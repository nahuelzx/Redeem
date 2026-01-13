<?php

declare(strict_types=1);

namespace nahu\redeem\util;

use pocketmine\item\Item;
use pocketmine\item\StringToItemParser;
use pocketmine\nbt\tag\CompoundTag;

final class Serialize {

    public static function getItem(string $item): Item {
        return StringToItemParser::getInstance()->parse($item);
    }

    public static function serialize(?Item $item): string {
        if ($item === null || $item->isNull()) {
            return "";
        }

        $payload = self::itemToJson($item);
        return base64_encode(gzcompress($payload));
    }

    public static function deserialize(string $item): ?Item {
        if ($item === "") {
            return null;
        }

        try {
            $decoded = base64_decode($item, true);
            if ($decoded === false) {
                return null;
            }

            $payload = gzuncompress($decoded);
            if ($payload === false) {
                return null;
            }

            $result = self::jsonToItem($payload);
            if ($result->isNull()) {
                return null;
            }

            return $result;
        } catch (\Throwable) {
            return null;
        }
    }

    public static function itemToJson(Item $item): string {
        $clone = clone $item;
        $nbt = $clone->nbtSerialize();
        return base64_encode(serialize($nbt));
    }

    public static function jsonToItem(string $json): Item {
        $decoded = base64_decode($json, true);
        if ($decoded === false) {
            return Item::null();
        }

        $nbt = @unserialize($decoded);
        if (!$nbt instanceof CompoundTag) {
            return Item::null();
        }

        return Item::nbtDeserialize($nbt);
    }
}
