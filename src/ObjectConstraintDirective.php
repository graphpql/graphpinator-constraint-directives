<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\ConstraintDirectives\Exception\AtLeastConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\AtMostConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\ExactlyConstraintNotSatisfied;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Attribute\Description;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Directive;
use Graphpinator\Typesystem\Field\FieldSet;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Typesystem\InterfaceType;
use Graphpinator\Typesystem\Location\InputObjectLocation;
use Graphpinator\Typesystem\Location\ObjectLocation;
use Graphpinator\Typesystem\Type;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\ListValue;
use Graphpinator\Value\NullValue;
use Graphpinator\Value\TypeValue;

#[Description('Graphpinator objectConstraint directive.')]
final class ObjectConstraintDirective extends Directive implements
    ObjectLocation,
    InputObjectLocation
{
    protected const NAME = 'objectConstraint';
    protected const REPEATABLE = true;

    public function __construct(
        private readonly ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
    }

    #[\Override]
    public function validateObjectUsage(Type|InterfaceType $type, ArgumentValueSet $arguments) : bool
    {
        return self::validateUsage($type->getFields(), $arguments);
    }

    #[\Override]
    public function validateInputUsage(InputType $inputType, ArgumentValueSet $arguments) : bool
    {
        return self::validateUsage($inputType->getArguments(), $arguments);
    }

    #[\Override]
    public function resolveObject(ArgumentValueSet $arguments, TypeValue $typeValue) : void
    {
        self::resolve($arguments, $typeValue);
    }

    #[\Override]
    public function resolveInputObject(ArgumentValueSet $arguments, InputValue $inputValue) : void
    {
        self::resolve($arguments, $inputValue);
    }

    #[\Override]
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
        $atLeastOne = $arguments->offsetGet('atLeastOne')->value;
        $atMostOne = $arguments->offsetGet('atMostOne')->value;
        $exactlyOne = $arguments->offsetGet('exactlyOne')->value;
        $atLeast = $arguments->offsetGet('atLeast')->value;
        $atMost = $arguments->offsetGet('atMost')->value;
        $exactly = $arguments->offsetGet('exactly')->value;

        return (!$atLeastOne instanceof ListValue || self::validateFieldsArePresent($fields, $atLeastOne))
            && (!$atMostOne instanceof ListValue || self::validateFieldsArePresent($fields, $atMostOne))
            && (!$exactlyOne instanceof ListValue || self::validateFieldsArePresent($fields, $exactlyOne))
            && (!$atLeast instanceof InputValue || self::validateObjectInput($fields, $atLeast))
            && (!$atMost instanceof InputValue || self::validateObjectInput($fields, $atMost))
            && (!$exactly instanceof InputValue || self::validateObjectInput($fields, $exactly));
    }

    private static function resolve(ArgumentValueSet $arguments, TypeValue|InputValue $value) : void
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->value->getRawValue();
        $atMostOne = $arguments->offsetGet('atMostOne')->value->getRawValue();
        $exactlyOne = $arguments->offsetGet('exactlyOne')->value->getRawValue();
        $atLeast = $arguments->offsetGet('atLeast')->value->getRawValue();
        $atMost = $arguments->offsetGet('atMost')->value->getRawValue();
        $exactly = $arguments->offsetGet('exactly')->value->getRawValue();

        \is_array($atLeastOne) && self::resolveAtLeast($value, $atLeastOne);
        \is_array($atMostOne) && self::resolveAtMost($value, $atMostOne);
        \is_array($exactlyOne) && self::resolveExactly($value, $exactlyOne);
        $atLeast instanceof \stdClass && self::resolveAtLeast($value, $atLeast->from, $atLeast->count);
        $atMost instanceof \stdClass && self::resolveAtMost($value, $atMost->from, $atMost->count);
        $exactly instanceof \stdClass && self::resolveExactly($value, $exactly->from, $exactly->count);
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
        return $object->value->count->getValue()->getRawValue() <= \count($object->value->from->getValue())
            && self::validateFieldsArePresent($fields, $object->value->from->getValue());
    }

    /**
     * @param list<string> $atLeast
     */
    private static function resolveAtLeast(TypeValue|InputValue $value, array $atLeast, int $count = 1) : bool
    {
        if ($value instanceof TypeValue) {
            [$currentCount, $notRequested] = self::countFieldsType($value, $atLeast);

            if ($currentCount + $notRequested < $count) {
                throw new AtLeastConstraintNotSatisfied();
            }

            return true;
        }

        if (self::countFieldsInput($value, $atLeast) < $count) {
            throw new AtLeastConstraintNotSatisfied();
        }

        return true;
    }

    /**
     * @param list<string> $atMost
     */
    private static function resolveAtMost(TypeValue|InputValue $value, array $atMost, int $count = 1) : bool
    {
        if ($value instanceof TypeValue) {
            [$currentCount] = self::countFieldsType($value, $atMost);

            if ($currentCount > $count) {
                throw new AtMostConstraintNotSatisfied();
            }

            return true;
        }

        if (self::countFieldsInput($value, $atMost) > $count) {
            throw new AtMostConstraintNotSatisfied();
        }

        return true;
    }

    /**
     * @param list<string> $exactly
     */
    private static function resolveExactly(TypeValue|InputValue $value, array $exactly, int $count = 1) : bool
    {
        if ($value instanceof TypeValue) {
            [$currentCount, $notRequested] = self::countFieldsType($value, $exactly);

            if ($currentCount > $count || ($currentCount + $notRequested) < $count) {
                throw new ExactlyConstraintNotSatisfied();
            }

            return true;
        }

        if (self::countFieldsInput($value, $exactly) !== $count) {
            throw new ExactlyConstraintNotSatisfied();
        }

        return true;
    }

    /**
     * @param list<string> $fields
     */
    private static function countFieldsInput(InputValue $value, array $fields) : int
    {
        $currentCount = 0;

        foreach ($fields as $fieldName) {
            if (!\property_exists($value->value, $fieldName) || $value->value->{$fieldName}->value instanceof NullValue) {
                continue;
            }

            ++$currentCount;
        }

        return $currentCount;
    }

    /**
     * @param list<string> $fields
     * @return array{0: int, 1: int}
     */
    private static function countFieldsType(TypeValue $value, array $fields) : array
    {
        $currentCount = 0;
        $notRequested = 0;

        foreach ($fields as $fieldName) {
            // fields were not requested and are not included in final value
            if (!\property_exists($value->value, $fieldName)) {
                ++$notRequested;

                continue;
            }

            if ($value->value->{$fieldName}->value instanceof NullValue) {
                continue;
            }

            ++$currentCount;
        }

        return [$currentCount, $notRequested];
    }
}
