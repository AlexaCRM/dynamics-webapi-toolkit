<?php
/**
 * Copyright 2018 AlexaCRM
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and
 * associated documentation files (the "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is furnished to do so,
 * subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR IMPLIED,
 * INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY, FITNESS FOR A PARTICULAR
 * PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS
 * BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION OF CONTRACT,
 * TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE
 * OR OTHER DEALINGS IN THE SOFTWARE.
 *
 */

namespace AlexaCRM\Enum;

use Elao\Enum\ChoiceEnumTrait;
use Elao\Enum\SimpleChoiceEnum;

/**
 * Provides implementation for choice-based enumerable types.
 */
abstract class ChoiceEnum extends SimpleChoiceEnum implements \JsonSerializable {

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

    /**
     * In JSON, serialize as string.
     *
     * @return string
     */
    public function jsonSerialize() {
        return $this->getReadable();
    }

}
