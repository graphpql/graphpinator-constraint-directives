<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

interface ConstraintDirectiveAccessor
{
    public function getString() : StringConstraintDirective;

    public function getInt() : IntConstraintDirective;

    public function getFloat() : FloatConstraintDirective;

    public function getList() : ListConstraintDirective;

    public function getListInput() : ListConstraintInput;

    public function getObjectInput() : ObjectConstraintInput;

    public function getObject() : ObjectConstraintDirective;
}
