<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MinItemsConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Min items constraint was not satisfied.';
}
