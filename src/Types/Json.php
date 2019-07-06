<?php

namespace LaravelDoctrine\ORM\Types;

use Doctrine\DBAL\Types\JsonType;

/**
 * @deprecated Extend JsonType directly
 *
 * This type was added when Doctrine only had `JsonArrayType`.
 * `JsonArrayType` had a flaw with empty/null values.
 * Doctrine later added a new type called `JsonType` without these
 * flaws, with the same logic as we had here. So this type is
 * now deprecated in favor of the official type.
 */
class Json extends JsonType
{
}
