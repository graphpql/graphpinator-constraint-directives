<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Contract\Value;
use Graphpinator\Value\ListValue;
use Graphpinator\Value\NullValue;
use Graphpinator\Value\VariableValue;

trait TScalarConstraint
{
    use TLeafConstraint;

    abstract protected function specificValidateValue(Value $value, ArgumentValueSet $arguments) : void;

    /**
     * @param list<string> $greater
     * @param list<string> $smaller
     */
    protected static function varianceValidateOneOf(array $greater, array $smaller) : bool
    {
        foreach ($smaller as $value) {
            if (!\in_array($value, $greater, true)) {
                return false;
            }
        }

        return true;
    }

    final protected function validateValue(Value $value, ArgumentValueSet $arguments) : void
    {
        if ($value instanceof NullValue) {
            return;
        }

        if ($value instanceof VariableValue) {
            $this->validateValue($value->getConcreteValue(), $arguments);

            return;
        }

        if ($value instanceof ListValue) {
            foreach ($value as $item) {
                $this->validateValue($item, $arguments);
            }

            return;
        }

        $this->specificValidateValue($value, $arguments);
    }
}
