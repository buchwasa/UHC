<?php
declare(strict_types=1);

namespace uhc\language;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

class Translation{
    /** @var string */
    private static $filePath;

    public function __construct(string $filePath)
    {
        self::$filePath = $filePath;
    }

    public static function convert(string $message, array $variables = []) : string{
        $config = new Config(self::$filePath, Config::JSON);
        $message = $config->get($message);
        $message = str_replace("&", TextFormat::ESCAPE, $message);

        return str_replace(array_keys($variables), $variables, $message);
    }
}