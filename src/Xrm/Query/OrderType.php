<?php

namespace AlexaCRM\Xrm\Query;

use AlexaCRM\Enum\ChoiceEnum;

/**
 * Contains the possible values for the order type.
 *
 * @method static Ascending() The values of the specified attribute should be sorted in ascending order, from lowest to highest.
 * @method static Descending() The values of the specified attribute should be sorted in descending order, from highest to lowest.
 */
final class OrderType extends ChoiceEnum {

    /**
     * The values of the specified attribute should be sorted in ascending order, from lowest to highest.
     */
    const Ascending = 0;

    /**
     * The values of the specified attribute should be sorted in descending order, from highest to lowest.
     */
    const Descending = 1;

}
