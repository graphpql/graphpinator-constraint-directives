<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use \Graphpinator\Typesystem\Argument\Argument;
use \Graphpinator\Typesystem\Container;
use \Graphpinator\Typesystem\Field\FieldSet;
use \Graphpinator\Typesystem\Argument\ArgumentSet;
use \Graphpinator\Value\ArgumentValueSet;
use \Graphpinator\Value\InputValue;
use \Graphpinator\Value\ListValue;
use \Graphpinator\Value\TypeValue;

final class ObjectConstraintDirective extends \Graphpinator\Typesystem\Directive implements
    \Graphpinator\Typesystem\Location\ObjectLocation,
    \Graphpinator\Typesystem\Location\InputObjectLocation
{
    protected const NAME = 'objectConstraint';
    protected const DESCRIPTION = 'Graphpinator objectConstraint directive.';
    protected const REPEATABLE = true;

    public function __construct(
        private ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
    }

    public function validateObjectUsage(\Graphpinator\Typesystem\Type|\Graphpinator\Typesystem\InterfaceType $type, ArgumentValueSet $arguments) : bool
    {
        return self::validateUsage($type->getFields(), $arguments);
    }

    public function validateInputUsage(\Graphpinator\Typesystem\InputType $inputType, ArgumentValueSet $arguments) : bool
    {
        return self::validateUsage($inputType->getArguments(), $arguments);
    }

    public function resolveObject(ArgumentValueSet $arguments, TypeValue $typeValue) : void
    {
        self::resolve($arguments, $typeValue);
    }

    public function resolveInputObject(ArgumentValueSet $arguments, InputValue $inputValue) : void
    {
        self::resolve($arguments, $inputValue);
    }

    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('atLeastOne', Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
            Argument::create('atMostOne', Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
            Argument::create('exactlyOne', Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
            Argument::create('atLeast', $this->constraintDirectiveAccessor->getObjectInput()),
            Argument::create('atMost', $this->constraintDirectiveAccessor->getObjectInput()),
            Argument::create('exactly', $this->constraintDirectiveAccessor->getObjectInput()),
        ]);
    }

    private static function validateUsage(FieldSet|ArgumentSet $fields, ArgumentValueSet $arguments) : bool
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->getValue();
        $atMostOne = $arguments->offsetGet('atMostOne')->getValue();
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue();
        $atLeast = $arguments->offsetGet('atLeast')->getValue();
        $atMost = $arguments->offsetGet('atMost')->getValue();
        $exactly = $arguments->offsetGet('exactly')->getValue();

        return (!$atLeastOne instanceof ListValue || self::validateFieldsArePresent($fields, $atLeastOne))
            && (!$atMostOne instanceof ListValue || self::validateFieldsArePresent($fields, $atMostOne))
            && (!$exactlyOne instanceof ListValue || self::validateFieldsArePresent($fields, $exactlyOne))
            && (!$atLeast instanceof InputValue || self::validateObjectInput($fields, $atLeast))
            && (!$atMost instanceof InputValue || self::validateObjectInput($fields, $atMost))
            && (!$exactly instanceof InputValue || self::validateObjectInput($fields, $exactly));
    }

    private static function resolve(ArgumentValueSet $arguments, TypeValue|InputValue $value) : void
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->getValue();
        $atMostOne = $arguments->offsetGet('atMostOne')->getValue();
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue();
        $atLeast = $arguments->offsetGet('atLeast')->getValue();
        $atMost = $arguments->offsetGet('atMost')->getValue();
        $exactly = $arguments->offsetGet('exactly')->getValue();

        $atLeastOne instanceof ListValue && self::resolveAtLeast($value, $atLeastOne->getRawValue());
        $atMostOne instanceof ListValue && self::resolveAtMost($value, $atMostOne->getRawValue());
        $exactlyOne instanceof ListValue && self::resolveExactly($value, $exactlyOne->getRawValue());
        $atLeast instanceof InputValue && self::resolveAtLeast(
            $value,
            $atLeast->from->getValue()->getRawValue(),
            $atLeast->count->getValue()->getRawValue(),
        );
        $atMost instanceof InputValue && self::resolveAtMost(
            $value,
            $atMost->from->getValue()->getRawValue(),
            $atMost->count->getValue()->getRawValue(),
        );
        $exactly instanceof InputValue && self::resolveExactly(
            $value,
            $exactly->from->getValue()->getRawValue(),
            $exactly->count->getValue()->getRawValue(),
        );
    }

    private static function validateFieldsArePresent(FieldSet|ArgumentSet $fields, ListValue $list) : bool
    {
        foreach ($list as $item) {
            if (!$fields->offsetExists($item->getRawValue())) {
                return false;
            }
        }

        return true;
    }

    private static function validateObjectInput(FieldSet|ArgumentSet $fields, InputValue $object) : bool
    {
        return $object->count->getValue()->getRawValue() <= \count($object->from->getValue())
            && self::validateFieldsArePresent($fields, $object->from->getValue());
    }

    private static function resolveAtLeast(TypeValue|InputValue $value, array $atLeast, int $count = 1) : void
    {
        if ($value instanceof TypeValue) {
            [$currentCount, $notRequested] = self::countFieldsType($value, $atLeast);

            if (($currentCount + $notRequested) < $count) {
                throw new Exception\AtLeastConstraintNotSatisfied();
            }

            return;
        }

        if (self::countFieldsInput($value, $atLeast) < $count) {
            throw new Exception\AtLeastConstraintNotSatisfied();
        }
    }

    private static function resolveAtMost(TypeValue|InputValue $value, array $atMost, int $count = 1) : void
    {
        if ($value instanceof TypeValue) {
            [$currentCount, $notRequested] = self::countFieldsType($value, $atMost);

            if ($currentCount > $count) {
                throw new Exception\AtMostConstraintNotSatisfied();
            }

            return;
        }

        if (self::countFieldsInput($value, $atMost) > $count) {
            throw new Exception\AtMostConstraintNotSatisfied();
        }
    }

    private static function resolveExactly(TypeValue|InputValue $value, array $exactly, int $count = 1) : void
    {
        if ($value instanceof TypeValue) {
            [$currentCount, $notRequested] = self::countFieldsType($value, $exactly);

            if ($currentCount > $count || ($currentCount + $notRequested) < $count) {
                throw new Exception\ExactlyConstraintNotSatisfied();
            }

            return;
        }

        if (self::countFieldsInput($value, $exactly) !== $count) {
            throw new Exception\ExactlyConstraintNotSatisfied();
        }
    }

    private static function countFieldsInput(InputValue $value, array $exactly) : int
    {
        $currentCount = 0;

        foreach ($exactly as $fieldName) {
            if (!isset($value->{$fieldName}) || $value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                continue;
            }

            ++$currentCount;
        }

        return $currentCount;
    }

    private static function countFieldsType(TypeValue $value, array $exactly) : array
    {
        $currentCount = 0;
        $notRequested = 0;

        foreach ($exactly as $fieldName) {
            // fields were not requested and are not included in final value
            if (!isset($value->{$fieldName})) {
                ++$notRequested;

                continue;
            }

            if ($value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                continue;
            }

            ++$currentCount;
        }

        return [$currentCount, $notRequested];
    }
}
