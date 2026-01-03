<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\InputType;

final class ListConstraintInput extends InputType
{
    protected const NAME = 'ListConstraintInput';

    public function __construct(
        private readonly ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
        parent::__construct();
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('minItems', Container::Int())
                ->setDefaultValue(null)
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 0],
                ),
            Argument::create('maxItems', Container::Int())
                ->setDefaultValue(null)
                ->addDirective(
                    $this->constraintDirectiveAccessor->getInt(),
                    ['min' => 0],
                ),
            Argument::create('unique', Container::Boolean()->notNull())
                ->setDefaultValue(false),
            Argument::create('innerList', $this)
                ->setDefaultValue(null),
        ]);
    }
}
