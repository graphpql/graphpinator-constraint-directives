<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

abstract class ConstraintError extends \Graphpinator\Exception\GraphpinatorBase
{
    final public function isOutputable() : bool
    {
        return true;
    }
}
