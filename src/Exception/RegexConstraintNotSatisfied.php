<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class RegexConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Regex constraint was not satisfied.';
}
