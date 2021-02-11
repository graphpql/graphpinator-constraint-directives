<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MinConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Min constraint was not satisfied.';
}
