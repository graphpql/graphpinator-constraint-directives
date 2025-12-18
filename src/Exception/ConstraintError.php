<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Exception;

use Graphpinator\Exception\GraphpinatorBase;

abstract class ConstraintError extends GraphpinatorBase
{
    #[\Override]
    final public function isOutputable() : bool
    {
        return true;
    }
}
