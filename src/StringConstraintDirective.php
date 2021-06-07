<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class StringConstraintDirective extends \Graphpinator\Typesystem\Directive implements
    \Graphpinator\Typesystem\Location\FieldDefinitionLocation,
    \Graphpinator\Typesystem\Location\ArgumentDefinitionLocation,
    \Graphpinator\Typesystem\Location\VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'stringConstraint';
    protected const DESCRIPTION = 'Graphpinator stringConstraint directive.';

    public function validateFieldUsage(
        \Graphpinator\Typesystem\Field\Field $field,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        $namedType = $field->getType()->getNamedType();

        return $namedType instanceof \Graphpinator\Typesystem\Spec\StringType
            || $namedType instanceof \Graphpinator\Typesystem\Spec\IdType;
    }

    public function validateArgumentUsage(
        \Graphpinator\Typesystem\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        $namedType = $argument->getType()->getNamedType();

        return $namedType instanceof \Graphpinator\Typesystem\Spec\StringType
            || $namedType instanceof \Graphpinator\Typesystem\Spec\IdType;
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        $namedType = $variable->getType()->getNamedType();

        return $namedType instanceof \Graphpinator\Typesystem\Spec\StringType
            || $namedType instanceof \Graphpinator\Typesystem\Spec\IdType;
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('minLength', \Graphpinator\Typesystem\Container::Int()),
            \Graphpinator\Typesystem\Argument\Argument::create('maxLength', \Graphpinator\Typesystem\Container::Int()),
            \Graphpinator\Typesystem\Argument\Argument::create('regex', \Graphpinator\Typesystem\Container::String()),
            \Graphpinator\Typesystem\Argument\Argument::create('oneOf', \Graphpinator\Typesystem\Container::String()->notNull()->list()),
        ]);
    }

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
        $minLength = $arguments->offsetGet('minLength')->getValue()->getRawValue();
        $maxLength = $arguments->offsetGet('maxLength')->getValue()->getRawValue();
        $regex = $arguments->offsetGet('regex')->getValue()->getRawValue();
        $oneOf = $arguments->offsetGet('oneOf')->getValue()->getRawValue();

        if (\is_int($minLength) && \mb_strlen($rawValue) < $minLength) {
            throw new Exception\MinLengthConstraintNotSatisfied();
        }

        if (\is_int($maxLength) && \mb_strlen($rawValue) > $maxLength) {
            throw new Exception\MaxLengthConstraintNotSatisfied();
        }

        if (\is_string($regex) && \preg_match($regex, $rawValue) !== 1) {
            throw new Exception\RegexConstraintNotSatisfied();
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
