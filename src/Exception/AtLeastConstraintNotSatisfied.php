<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class AtLeastConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'AtLeast constraint was not satisfied.';
}
