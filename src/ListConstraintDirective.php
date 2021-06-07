<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class ListConstraintDirective extends \Graphpinator\Typesystem\Directive implements
    \Graphpinator\Typesystem\Location\FieldDefinitionLocation,
    \Graphpinator\Typesystem\Location\ArgumentDefinitionLocation,
    \Graphpinator\Typesystem\Location\VariableDefinitionLocation
{
    use TLeafConstraint;

    protected const NAME = 'listConstraint';
    protected const DESCRIPTION = 'Graphpinator listConstraint directive.';

    public function validateFieldUsage(
        \Graphpinator\Typesystem\Field\Field $field,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return self::recursiveValidateType($field->getType(), (object) $arguments->getValuesForResolver());
    }

    public function validateArgumentUsage(
        \Graphpinator\Typesystem\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return self::recursiveValidateType($argument->getType(), (object) $arguments->getValuesForResolver());
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return self::recursiveValidateType($variable->getType(), (object) $arguments->getValuesForResolver());
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return $this->constraintDirectiveAccessor->getListInput()->getArguments();
    }

    protected function validateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        if ($value instanceof \Graphpinator\Value\NullValue) {
            return;
        }

        \assert($value instanceof \Graphpinator\Value\ListValue);

        self::recursiveValidate($value->getRawValue(), (object) $arguments->getValuesForResolver());
    }

    protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        self::recursiveSpecificValidateVariance(
            (object) $biggerSet->getValuesForResolver(),
            (object) $smallerSet->getValuesForResolver(),
        );
    }

    private static function recursiveValidateType(
        \Graphpinator\Typesystem\Contract\Type $type,
        \stdClass $options,
    ) : bool
    {
        $usedType = $type->getShapingType();

        if (!$usedType instanceof \Graphpinator\Typesystem\ListType) {
            return false;
        }

        $usedType = $usedType->getInnerType()->getShapingType();

        if ($options->unique && !$usedType instanceof \Graphpinator\Typesystem\Contract\LeafType) {
            throw new Exception\UniqueConstraintOnlyScalar();
        }

        if ($options->innerList instanceof \stdClass) {
            return self::recursiveValidateType($usedType, $options->innerList);
        }

        return true;
    }

    private static function recursiveValidate(array $rawValue, \stdClass $options) : void
    {
        if (\is_int($options->minItems) && \count($rawValue) < $options->minItems) {
            throw new Exception\MinItemsConstraintNotSatisfied();
        }

        if (\is_int($options->maxItems) && \count($rawValue) > $options->maxItems) {
            throw new Exception\MaxItemsConstraintNotSatisfied();
        }

        if ($options->unique) {
            $differentValues = [];

            foreach ($rawValue as $innerValue) {
                if (!\array_key_exists($innerValue, $differentValues)) {
                    $differentValues[$innerValue] = true;

                    continue;
                }

                throw new Exception\UniqueConstraintNotSatisfied();
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

    private static function recursiveSpecificValidateVariance(
        \stdClass $greater,
        \stdClass $smaller,
    ) : void
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
