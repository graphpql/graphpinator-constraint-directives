<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class OneOfConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'OneOf constraint was not satisfied.';
}
