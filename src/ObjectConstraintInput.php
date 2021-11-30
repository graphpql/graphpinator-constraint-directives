<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class ObjectConstraintInput extends \Graphpinator\Typesystem\InputType
{
    protected const NAME = 'ObjectConstraintInput';

    public function __construct(
        private ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
        parent::__construct();
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('count', \Graphpinator\Typesystem\Container::Int()->notNull())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 1],
                ),
            \Graphpinator\Typesystem\Argument\Argument::create('from', \Graphpinator\Typesystem\Container::String()->notNullList())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
        ]);
    }
}
