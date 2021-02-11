<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MaxConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Max constraint was not satisfied.';
}
