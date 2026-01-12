<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinLengthConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\RegexConstraintNotSatisfied;
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
use Graphpinator\Typesystem\Spec\IdType;
use Graphpinator\Typesystem\Spec\StringType;
use Graphpinator\Typesystem\Visitor\GetNamedTypeVisitor;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Contract\Value;

#[Description('Graphpinator stringConstraint directive.')]
final class StringConstraintDirective extends Directive implements
    FieldDefinitionLocation,
    ArgumentDefinitionLocation,
    VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'stringConstraint';

    #[\Override]
    public function validateFieldUsage(Field $field, ArgumentValueSet $arguments) : bool
    {
        $namedType = $field->getType()->accept(new GetNamedTypeVisitor());

        return $namedType instanceof StringType
            || $namedType instanceof IdType;
    }

    #[\Override]
    public function validateArgumentUsage(Argument $argument, ArgumentValueSet $arguments) : bool
    {
        $namedType = $argument->getType()->accept(new GetNamedTypeVisitor());

        return $namedType instanceof StringType
            || $namedType instanceof IdType;
    }

    #[\Override]
    public function validateVariableUsage(Variable $variable, ArgumentValueSet $arguments) : bool
    {
        $namedType = $variable->type->accept(new GetNamedTypeVisitor());

        return $namedType instanceof StringType
            || $namedType instanceof IdType;
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('minLength', Container::Int()),
            Argument::create('maxLength', Container::Int()),
            Argument::create('regex', Container::String()),
            Argument::create('oneOf', Container::String()->notNull()->list()),
        ]);
    }

    #[\Override]
    protected function afterGetFieldDefinition() : void
    {
        $this->arguments['minLength']->addDirective(
            $this->constraintDirectiveAccessor->getInt(),
            ['min' => 0],
        );
        $this->arguments['maxLength']->addDirective(
            $this->constraintDirectiveAccessor->getInt(),
            ['min' => 0],
        );
    }

    #[\Override]
    protected function specificValidateValue(Value $value, ArgumentValueSet $arguments) : void
    {
        $rawValue = $value->getRawValue();
        $minLength = $arguments->offsetGet('minLength')->value->getRawValue();
        $maxLength = $arguments->offsetGet('maxLength')->value->getRawValue();
        $regex = $arguments->offsetGet('regex')->value->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->value->getRawValue();

        if (\is_int($minLength) && \mb_strlen($rawValue) < $minLength) {
            throw new MinLengthConstraintNotSatisfied();
        }

        if (\is_int($maxLength) && \mb_strlen($rawValue) > $maxLength) {
            throw new MaxLengthConstraintNotSatisfied();
        }

        // @phpstan-ignore theCodingMachineSafe.function
        if (\is_string($regex) && \preg_match($regex, $rawValue) !== 1) {
            throw new RegexConstraintNotSatisfied();
        }

        if (\is_array($oneOf) && !\in_array($rawValue, $oneOf, true)) {
            throw new OneOfConstraintNotSatisfied();
        }
    }

    #[\Override]
    protected function specificValidateVariance(ArgumentValueSet $biggerSet, ArgumentValueSet $smallerSet) : void
    {
        $lhs = $biggerSet->getValuesForResolver();
        $rhs = $smallerSet->getValuesForResolver();

        if (\is_int($lhs['minLength']) && ($rhs['minLength'] === null || $rhs['minLength'] < $lhs['minLength'])) {
            throw new \Exception();
        }

        if (\is_int($lhs['maxLength']) && ($rhs['maxLength'] === null || $rhs['maxLength'] > $lhs['maxLength'])) {
            throw new \Exception();
        }

        if (\is_string($lhs['regex']) && ($rhs['regex'] === null || $rhs['regex'] !== $lhs['regex'])) {
            throw new \Exception();
        }

        if (\is_array($lhs['oneOf']) && ($rhs['oneOf'] === null || !self::varianceValidateOneOf($lhs['oneOf'], $rhs['oneOf']))) {
            throw new \Exception();
        }
    }
}
