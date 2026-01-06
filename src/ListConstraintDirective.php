<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\UniqueConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\UniqueConstraintOnlyScalar;
use Graphpinator\Normalizer\Variable\Variable;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Attribute\Description;
use Graphpinator\Typesystem\Contract\LeafType;
use Graphpinator\Typesystem\Contract\Type;
use Graphpinator\Typesystem\Directive;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\ListType;
use Graphpinator\Typesystem\Location\ArgumentDefinitionLocation;
use Graphpinator\Typesystem\Location\FieldDefinitionLocation;
use Graphpinator\Typesystem\Location\VariableDefinitionLocation;
use Graphpinator\Typesystem\Visitor\GetShapingTypeVisitor;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Contract\Value;
use Graphpinator\Value\ListValue;
use Graphpinator\Value\NullValue;

#[Description('Graphpinator listConstraint directive.')]
final class ListConstraintDirective extends Directive implements
    FieldDefinitionLocation,
    ArgumentDefinitionLocation,
    VariableDefinitionLocation
{
    use TLeafConstraint;

    protected const NAME = 'listConstraint';

    #[\Override]
    public function validateFieldUsage(Field $field, ArgumentValueSet $arguments) : bool
    {
        return self::recursiveValidateType($field->getType(), (object) $arguments->getValuesForResolver());
    }

    #[\Override]
    public function validateArgumentUsage(Argument $argument, ArgumentValueSet $arguments) : bool
    {
        return self::recursiveValidateType($argument->getType(), (object) $arguments->getValuesForResolver());
    }

    #[\Override]
    public function validateVariableUsage(Variable $variable, ArgumentValueSet $arguments) : bool
    {
        return self::recursiveValidateType($variable->type, (object) $arguments->getValuesForResolver());
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return $this->constraintDirectiveAccessor->getListInput()->getArguments();
    }

    #[\Override]
    protected function validateValue(Value $value, ArgumentValueSet $arguments) : void
    {
        if ($value instanceof NullValue) {
            return;
        }

        \assert($value instanceof ListValue);

        self::recursiveValidate($value->getRawValue(), (object) $arguments->getValuesForResolver());
    }

    #[\Override]
    protected function specificValidateVariance(ArgumentValueSet $biggerSet, ArgumentValueSet $smallerSet) : void
    {
        self::recursiveSpecificValidateVariance(
            (object) $biggerSet->getValuesForResolver(),
            (object) $smallerSet->getValuesForResolver(),
        );
    }

    private static function recursiveValidateType(Type $type, \stdClass $options) : bool
    {
        $usedType = $type->accept(new GetShapingTypeVisitor());

        if (!$usedType instanceof ListType) {
            return false;
        }

        $usedType = $usedType->getInnerType()->accept(new GetShapingTypeVisitor());

        if ($options->unique && !$usedType instanceof LeafType) {
            throw new UniqueConstraintOnlyScalar();
        }

        if ($options->innerList instanceof \stdClass) {
            return self::recursiveValidateType($usedType, $options->innerList);
        }

        return true;
    }

    /**
     * @param list<mixed> $rawValue
     */
    private static function recursiveValidate(array $rawValue, \stdClass $options) : void
    {
        if (\is_int($options->minItems) && \count($rawValue) < $options->minItems) {
            throw new MinItemsConstraintNotSatisfied();
        }

        if (\is_int($options->maxItems) && \count($rawValue) > $options->maxItems) {
            throw new MaxItemsConstraintNotSatisfied();
        }

        if ($options->unique) {
            $differentValues = [];

            foreach ($rawValue as $innerValue) {
                $innerValue = $innerValue instanceof \BackedEnum
                    ? $innerValue->value
                    : $innerValue;
                
                if (\array_key_exists($innerValue, $differentValues)) {
                    throw new UniqueConstraintNotSatisfied();
                }
            }
        }

        if (!$options->innerList instanceof \stdClass) {
            return;
        }

        foreach ($rawValue as $innerValue) {
            if ($innerValue === null) {
                continue;
            }

            self::recursiveValidate($innerValue, $options->innerList);
        }
    }

    private static function recursiveSpecificValidateVariance(\stdClass $greater, \stdClass $smaller) : void
    {
        if (\is_int($greater->minItems) && ($smaller->minItems === null || $smaller->minItems < $greater->minItems)) {
            throw new \Exception();
        }

        if (\is_int($greater->maxItems) && ($smaller->maxItems === null || $smaller->maxItems > $greater->maxItems)) {
            throw new \Exception();
        }

        if ($greater->unique === true && ($smaller->unique === null || $smaller->unique === false)) {
            throw new \Exception();
        }

        if (!($greater->innerList instanceof \stdClass)) {
            return;
        }

        if ($smaller->innerList === null) {
            throw new \Exception();
        }

        self::recursiveSpecificValidateVariance($greater->innerList, $smaller->innerList);
    }
}
