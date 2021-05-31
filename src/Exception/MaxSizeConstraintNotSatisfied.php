<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MaxSizeConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Max size constraint was not satisfied.';
}
