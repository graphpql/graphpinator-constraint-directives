<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class FloatConstraintDirective extends LeafConstraintDirective
{
    protected const NAME = 'floatConstraint';
    protected const DESCRIPTION = 'Graphpinator floatConstraint directive.';

    public function validateFieldUsage(
        \Graphpinator\Field\Field $field,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $field->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\FloatType;
    }

    public function validateArgumentUsage(
        \Graphpinator\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $argument->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\FloatType;
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ): bool
    {
        return $variable->getType()->getNamedType() instanceof \Graphpinator\Type\Spec\FloatType;
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('min', \Graphpinator\Container\Container::Float()),
            \Graphpinator\Argument\Argument::create('max', \Graphpinator\Container\Container::Float()),
            \Graphpinator\Argument\Argument::create('oneOf', \Graphpinator\Container\Container::Float()->notNull()->list()),
        ]);
    }

    protected function afterGetFieldDefinition() : void
    {
        $this->arguments['oneOf']->addDirective(
            $this->constraintDirectiveAccessor->getList(),
            ['minItems' => 1],
        );
    }

    protected function specificValidateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $rawValue = $value->getRawValue();
        $min = $arguments->offsetGet('min')->getValue()->getRawValue();
        $max = $arguments->offsetGet('max')->getValue()->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->getValue()->getRawValue();

        if (\is_float($min) && $rawValue < $min) {
            throw new Exception\MinConstraintNotSatisfied();
        }

        if (\is_float($max) && $rawValue > $max) {
            throw new Exception\MaxConstraintNotSatisfied();
        }

        if (\is_array($oneOf) && !\in_array($rawValue, $oneOf, true)) {
            throw new Exception\OneOfConstraintNotSatisfied();
        }
    }

    protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        $lhs = $biggerSet->getValuesForResolver();
        $rhs = $smallerSet->getValuesForResolver();

        if (\is_float($lhs['min']) && ($rhs['min'] === null || $rhs['min'] < $lhs['min'])) {
            throw new \Exception();
        }

        if (\is_float($lhs['max']) && ($rhs['max'] === null || $rhs['max'] > $lhs['max'])) {
            throw new \Exception();
        }

        if (\is_array($lhs['oneOf']) && ($rhs['oneOf'] === null || !self::varianceValidateOneOf($lhs['oneOf'], $rhs['oneOf']))) {
            throw new \Exception();
        }
    }
}
