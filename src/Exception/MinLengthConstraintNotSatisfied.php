<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MinLengthConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Min length constraint was not satisfied.';
}
