<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class ListConstraintInput extends \Graphpinator\Typesystem\InputType
{
    protected const NAME = 'ListConstraintInput';

    public function __construct(
        private ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
        parent::__construct();
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('minItems', \Graphpinator\Typesystem\Container::Int())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 0],
                ),
            \Graphpinator\Typesystem\Argument\Argument::create('maxItems', \Graphpinator\Typesystem\Container::Int())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 0],
                ),
            \Graphpinator\Typesystem\Argument\Argument::create('unique', \Graphpinator\Typesystem\Container::Boolean()->notNull())
                ->setDefaultValue(false),
            \Graphpinator\Typesystem\Argument\Argument::create('innerList', $this),
        ]);
    }
}
