<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\InputType;

final class ObjectConstraintInput extends InputType
{
    protected const NAME = 'ObjectConstraintInput';

    public function __construct(
        private ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
        parent::__construct();
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('count', Container::Int()->notNull())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 1],
                ),
            Argument::create('from', Container::String()->notNullList())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
        ]);
    }
}
