<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class AtMostConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'AtMost constraint was not satisfied.';
}
