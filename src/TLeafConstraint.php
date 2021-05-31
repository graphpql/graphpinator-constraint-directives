<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

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
        ?\Graphpinator\Value\ArgumentValueSet $biggerSet,
        ?\Graphpinator\Value\ArgumentValueSet $smallerSet,
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
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\ResolvedValue $parentValue,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionBefore(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\ResolvedValue $parentValue,
        \Graphpinator\Value\ArgumentValueSet $fieldArguments,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionAfter(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\ResolvedValue $resolvedValue,
        \Graphpinator\Value\ArgumentValueSet $fieldArguments,
    ) : void
    {
        // nothing here
    }

    final public function resolveFieldDefinitionValue(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\FieldValue $fieldValue,
    ) : void
    {
        $this->validateValue($fieldValue->getValue(), $arguments);
    }

    final public function resolveArgumentDefinition(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\ArgumentValue $argumentValue,
    ) : void
    {
        $this->validateValue($argumentValue->getValue(), $arguments);
    }

    final public function resolveVariableDefinition(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\InputedValue $variableValue,
    ) : void
    {
        $this->validateValue($variableValue, $arguments);
    }

    abstract protected function validateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void;

    abstract protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void;
}
