<?php

namespace AlexaCRM\Xrm;

use AlexaCRM\Enum\ChoiceEnum;

/**
 * Contains values to indicate the role the entity plays in a relationship.
 *
 * @method static Referenced() Specifies that the entity is the referenced entity.
 * @method static Referencing() Specifies that the entity is the referencing entity.
 */
final class EntityRole extends ChoiceEnum {

    /**
     * Specifies that the entity is the referenced entity.
     */
    const Referenced = 1;

    /**
     * Specifies that the entity is the referencing entity.
     */
    const Referencing = 0;

}
