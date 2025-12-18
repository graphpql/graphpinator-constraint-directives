<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\Value\ArgumentValue;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\FieldValue;
use Graphpinator\Value\InputedValue;
use Graphpinator\Value\ResolvedValue;
use Graphpinator\Value\Value;

trait TLeafConstraint
{
    public function __construct(
        protected ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
    }

    final public static function isPure() : bool
    {
        return true;
    }

    final public function validateVariance(
        ?ArgumentValueSet $biggerSet,
        ?ArgumentValueSet $smallerSet,
    ) : void
    {
        if ($biggerSet === null) {
            return;
        }

        if ($smallerSet === null) {
            throw new \Exception();
        }

        $this->specificValidateVariance($biggerSet, $smallerSet);
    }

    final public function resolveFieldDefinitionStart(
        ArgumentValueSet $arguments,
        ResolvedValue $parentValue,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionBefore(
        ArgumentValueSet $arguments,
        ResolvedValue $parentValue,
        ArgumentValueSet $fieldArguments,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionAfter(
        ArgumentValueSet $arguments,
        ResolvedValue $resolvedValue,
        ArgumentValueSet $fieldArguments,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionValue(
        ArgumentValueSet $arguments,
        FieldValue $fieldValue,
    ) : void
    {
        $this->validateValue($fieldValue->getValue(), $arguments);
    }

    final public function resolveArgumentDefinition(
        ArgumentValueSet $arguments,
        ArgumentValue $argumentValue,
    ) : void
    {
        $this->validateValue($argumentValue->getValue(), $arguments);
    }

    final public function resolveVariableDefinition(
        ArgumentValueSet $arguments,
        InputedValue $variableValue,
    ) : void
    {
        $this->validateValue($variableValue, $arguments);
    }

    abstract protected function validateValue(
        Value $value,
        ArgumentValueSet $arguments,
    ) : void;

    abstract protected function specificValidateVariance(
        ArgumentValueSet $biggerSet,
        ArgumentValueSet $smallerSet,
    ) : void;
}
