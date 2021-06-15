<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

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

    public function validateObjectUsage(
        \Graphpinator\Typesystem\Type|\Graphpinator\Typesystem\InterfaceType $type,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $this->validateUsage($type->getFields(), $arguments);
    }

    public function validateInputUsage(
        \Graphpinator\Typesystem\InputType $inputType,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $this->validateUsage($inputType->getArguments(), $arguments);
    }

    public function resolveObject(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\TypeValue $typeValue,
    ) : void
    {
        $this->validateAtLeastOne($typeValue, $arguments);
        $this->validateExactlyOneType($typeValue, $arguments);
    }

    public function resolveInputObject(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\InputValue $inputValue,
    ) : void
    {
        $this->validateAtLeastOne($inputValue, $arguments);
        $this->validateExactlyOneInput($inputValue, $arguments);
    }

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('atLeastOne', \Graphpinator\Typesystem\Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
            \Graphpinator\Typesystem\Argument\Argument::create('exactlyOne', \Graphpinator\Typesystem\Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
        ]);
    }

    private function validateUsage(
        \Graphpinator\Typesystem\Field\FieldSet|\Graphpinator\Typesystem\Argument\ArgumentSet $fields,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->getValue();
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue();

        if ($atLeastOne instanceof \Graphpinator\Value\ListValue) {
            foreach ($atLeastOne as $item) {
                if (!$fields->offsetExists($item->getRawValue())) {
                    return false;
                }
            }
        }

        if ($exactlyOne instanceof \Graphpinator\Value\ListValue) {
            foreach ($exactlyOne as $item) {
                if (!$fields->offsetExists($item->getRawValue())) {
                    return false;
                }
            }
        }

        return true;
    }

    private function validateAtLeastOne(
        \Graphpinator\Value\TypeValue|\Graphpinator\Value\InputValue $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->getValue()->getRawValue();

        if (!\is_array($atLeastOne)) {
            return;
        }

        foreach ($atLeastOne as $fieldName) {
            if (!isset($value->{$fieldName}) || $value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                continue;
            }

            return;
        }

        throw new Exception\AtLeastOneConstraintNotSatisfied();
    }

    private function validateExactlyOneInput(
        \Graphpinator\Value\InputValue $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue()->getRawValue();

        if (!\is_array($exactlyOne)) {
            return;
        }

        $count = 0;

        foreach ($exactlyOne as $fieldName) {
            if (!isset($value->{$fieldName}) || $value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                continue;
            }

            ++$count;
        }

        if ($count !== 1) {
            throw new Exception\ExactlyOneConstraintNotSatisfied();
        }
    }

    private function validateExactlyOneType(
        \Graphpinator\Value\TypeValue $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue()->getRawValue();

        if (!\is_array($exactlyOne)) {
            return;
        }

        $count = 0;
        $notRequested = 0;

        foreach ($exactlyOne as $fieldName) {
            // fields were not requested and are not included in final value
            if (!isset($value->{$fieldName})) {
                ++$notRequested;

                continue;
            }

            if ($value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                continue;
            }

            ++$count;
        }

        if ($count > 1 || ($count === 0 && $notRequested === 0)) {
            throw new Exception\ExactlyOneConstraintNotSatisfied();
        }
    }
}
