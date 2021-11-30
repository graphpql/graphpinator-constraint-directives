<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

final class AtMostOneConstraintNotSatisfied extends ConstraintError
{
    public const MESSAGE = 'AtMostOne constraint was not satisfied.';
}
