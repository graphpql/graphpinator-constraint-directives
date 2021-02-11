<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MaxLengthConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Max length constraint was not satisfied.';
}
