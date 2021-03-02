<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class ObjectConstraintDirective extends \Graphpinator\Directive\Directive
    implements \Graphpinator\Directive\Contract\ObjectLocation, \Graphpinator\Directive\Contract\InputObjectLocation
{
    protected const NAME = 'objectConstraint';
    protected const DESCRIPTION = 'Graphpinator objectConstraint directive.';
    protected const REPEATABLE = true;

    public function __construct(
        private ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    ) {}

    public function validateObjectUsage(
        \Graphpinator\Type\Type|\Graphpinator\Type\InterfaceType $type,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $this->validateUsage($type->getFields(), $arguments);
    }

    public function validateInputUsage(
        \Graphpinator\Type\InputType $inputType,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ): bool
    {
        return $this->validateUsage($inputType->getArguments(), $arguments);
    }

    public function resolveObject(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\TypeValue $typeValue,
    ) : void
    {
        $this->validate($typeValue, $arguments);
    }

    public function resolveInputObject(
        \Graphpinator\Value\ArgumentValueSet $arguments,
        \Graphpinator\Value\InputValue $inputValue,
    ) : void
    {
        $this->validate($inputValue, $arguments);
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('atLeastOne', \Graphpinator\Container\Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
            \Graphpinator\Argument\Argument::create('exactlyOne', \Graphpinator\Container\Container::String()->notNull()->list())
                ->addDirective(
                    $this->constraintDirectiveAccessor->getList(),
                    ['minItems' => 1],
                ),
        ]);
    }

    private function validateUsage(
        \Graphpinator\Field\FieldSet|\Graphpinator\Argument\ArgumentSet $fields,
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

    private function validate(
        \Graphpinator\Value\TypeValue|\Graphpinator\Value\InputValue $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $atLeastOne = $arguments->offsetGet('atLeastOne')->getValue()->getRawValue();
        $exactlyOne = $arguments->offsetGet('exactlyOne')->getValue()->getRawValue();

        if (\is_array($atLeastOne)) {
            $valid = false;

            foreach ($atLeastOne as $fieldName) {
                if (isset($value->{$fieldName}) && $value->{$fieldName}->getValue() instanceof \Graphpinator\Value\NullValue) {
                    continue;
                }

                $valid = true;

                break;
            }

            if (!$valid) {
                throw new Exception\AtLeastOneConstraintNotSatisfied();
            }
        }

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
