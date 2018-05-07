<?php

namespace AlexaCRM\Enum;

use Elao\Enum\AutoDiscoveredValuesTrait;

abstract class FlaggedEnum extends \Elao\Enum\FlaggedEnum {

    use AutoDiscoveredValuesTrait;

    /** @var array */
    private static $readables = [];

    /**
     * {@inheritdoc}
     */
    public static function readables(): array
    {
        $enumType = static::class;

        if (!isset(self::$readables[$enumType])) {
            $constants = (new \ReflectionClass($enumType))->getConstants();
            foreach (static::values() as $value) {
                $constantName = array_search($value, $constants, true);
                self::$readables[$enumType][$value] = $constantName;
            }
        }

        return self::$readables[$enumType];
    }

}
