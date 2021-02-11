<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class MaxItemsConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Max items constraint was not satisfied.';
}
