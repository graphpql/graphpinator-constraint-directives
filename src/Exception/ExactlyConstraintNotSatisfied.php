<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class ExactlyConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'Exactly constraint was not satisfied.';
}
