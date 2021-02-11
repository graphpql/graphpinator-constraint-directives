<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class AtLeastOneConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'AtLeastOne constraint was not satisfied.';
}
