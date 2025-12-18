<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied;
use Graphpinator\Normalizer\Variable\Variable;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Attribute\Description;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Directive;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\Location\ArgumentDefinitionLocation;
use Graphpinator\Typesystem\Location\FieldDefinitionLocation;
use Graphpinator\Typesystem\Location\VariableDefinitionLocation;
use Graphpinator\Typesystem\Spec\FloatType;
use Graphpinator\Typesystem\Visitor\GetNamedTypeVisitor;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Value;

#[Description('Graphpinator floatConstraint directive.')]
final class FloatConstraintDirective extends Directive implements
    FieldDefinitionLocation,
    ArgumentDefinitionLocation,
    VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'floatConstraint';

    #[\Override]
    public function validateFieldUsage(
        Field $field,
        ArgumentValueSet $arguments,
    ) : bool
    {
        return $field->getType()->accept(new GetNamedTypeVisitor()) instanceof FloatType;
    }

    #[\Override]
    public function validateArgumentUsage(
        Argument $argument,
        ArgumentValueSet $arguments,
    ) : bool
    {
        return $argument->getType()->accept(new GetNamedTypeVisitor()) instanceof FloatType;
    }

    #[\Override]
    public function validateVariableUsage(
        Variable $variable,
        ArgumentValueSet $arguments,
    ) : bool
    {
        return $variable->getType()->accept(new GetNamedTypeVisitor()) instanceof FloatType;
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('min', Container::Float()),
            Argument::create('max', Container::Float()),
            Argument::create('oneOf', Container::Float()->notNull()->list()),
        ]);
    }

    #[\Override]
    protected function afterGetFieldDefinition() : void
    {
        $this->arguments['oneOf']->addDirective(
            $this->constraintDirectiveAccessor->getList(),
            ['minItems' => 1],
        );
    }

    #[\Override]
    protected function specificValidateValue(
        Value $value,
        ArgumentValueSet $arguments,
    ) : void
    {
        $rawValue = $value->getRawValue();
        $min = $arguments->offsetGet('min')->getValue()->getRawValue();
        $max = $arguments->offsetGet('max')->getValue()->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->getValue()->getRawValue();

        if (\is_float($min) && $rawValue < $min) {
            throw new MinConstraintNotSatisfied();
        }

        if (\is_float($max) && $rawValue > $max) {
            throw new MaxConstraintNotSatisfied();
        }

        if (\is_array($oneOf) && !\in_array($rawValue, $oneOf, true)) {
            throw new OneOfConstraintNotSatisfied();
        }
    }

    #[\Override]
    protected function specificValidateVariance(
        ArgumentValueSet $biggerSet,
        ArgumentValueSet $smallerSet,
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
