<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class UniqueConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Unique constraint was not satisfied.';
}
