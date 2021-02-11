<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class ExactlyOneConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'ExactlyOne constraint was not satisfied.';
}
