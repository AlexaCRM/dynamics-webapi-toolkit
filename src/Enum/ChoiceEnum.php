<?php

namespace AlexaCRM\Enum;

use Elao\Enum\ChoiceEnumTrait;
use Elao\Enum\SimpleChoiceEnum;

abstract class ChoiceEnum extends SimpleChoiceEnum {

    /** @internal */
    private static $guessedReadables = [];

    /** @internal */
    private static $guessedValues = [];

    /**
     * @internal
     */
    private static function autodiscoveredValues(): array
    {
        $enumType = static::class;

        if (!isset(self::$guessedValues[$enumType])) {
            $r = new \ReflectionClass($enumType);
            $values = $r->getConstants();

            if (PHP_VERSION_ID >= 70100) {
                $values = array_filter($values, function (string $k) use ($r) {
                    return $r->getReflectionConstant($k)->isPublic();
                }, ARRAY_FILTER_USE_KEY);
            }

            if (is_a($enumType, FlaggedEnum::class, true)) {
                $values = array_filter($values, function ($v) {
                    return \is_int($v) && 0 === ($v & $v - 1) && $v > 0;
                });
            } else {
                $values = array_filter($values, function ($v) {
                    return \is_int($v) || \is_string($v);
                });
            }

            self::$guessedValues[$enumType] = array_values($values);
        }

        return self::$guessedValues[$enumType];
    }

    /**
     * @see ChoiceEnumTrait::choices()
     */
    protected static function choices(): array
    {
        $enumType = static::class;

        if (!isset(self::$guessedReadables[$enumType])) {
            $constants = (new \ReflectionClass($enumType))->getConstants();
            foreach (self::autodiscoveredValues() as $value) {
                $constantName = array_search($value, $constants, true);
                self::$guessedReadables[$enumType][$value] = $constantName;
            }
        }

        return self::$guessedReadables[$enumType];
    }

}
