<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class UploadConstraintDirective extends \Graphpinator\Directive\Directive
    implements \Graphpinator\Directive\Contract\ArgumentDefinitionLocation
{
    protected const NAME = 'uploadConstraint';
    protected const DESCRIPTION = 'Graphpinator uploadConstraint directive.';

    public function __construct(
        protected ConstraintDirectiveAccessor $constraintDirectiveAccessor,
    )
    {
        parent::__construct(
            [
                \Graphpinator\Directive\TypeSystemDirectiveLocation::ARGUMENT_DEFINITION,
                \Graphpinator\Directive\TypeSystemDirectiveLocation::INPUT_FIELD_DEFINITION,
            ],
            false,
        );
    }

    public function validateType(
        \Graphpinator\Type\Contract\Definition $definition,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $definition->getNamedType() instanceof \Graphpinator\Module\Upload\UploadType;
    }

    final public function validateVariance(
        ?\Graphpinator\Value\ArgumentValueSet $biggerSet,
        ?\Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        if ($biggerSet === null) {
            return;
        }

        if ($smallerSet === null) {
            throw new \Exception();
        }

        $this->specificValidateVariance($biggerSet, $smallerSet);
    }

    public function resolveArgumentDefinition(
        \Graphpinator\Value\ArgumentValue $argumentValue,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        $this->validateValue($argumentValue->getValue(), $arguments);
    }

    final protected function validateValue(
        \Graphpinator\Value\Value $value,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : void
    {
        if ($value instanceof \Graphpinator\Value\NullValue) {
            return;
        }

        if ($value instanceof \Graphpinator\Value\ListValue) {
            foreach ($value as $item) {
                $this->validateValue($item, $arguments);
            }

            return;
        }

        $this->specificValidateValue($value, $arguments);
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('maxSize', \Graphpinator\Container\Container::Int()),
            \Graphpinator\Argument\Argument::create('mimeType', \Graphpinator\Container\Container::String()->notNull()->list()),
        ]);
    }

    protected function appendDirectives(): void
    {
        $this->arguments['maxSize']->addDirective(
            $this->constraintDirectiveAccessor->getInt(),
            ['min' => 0],
        );
        $this->arguments['mimeType']->addDirective(
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
        $maxSize = $arguments->offsetGet('maxSize')->getValue()->getRawValue();
        $mimeType = $arguments->offsetGet('mimeType')->getValue()->getRawValue();

        if (\is_int($maxSize) && $rawValue->getSize() > $maxSize) {
            throw new \Graphpinator\ConstraintDirectives\Exception\MaxSizeConstraintNotSatisfied();
        }

        if (\is_array($mimeType) && !\in_array(\mime_content_type($rawValue->getStream()->getMetadata('uri')), $mimeType, true)) {
            throw new \Graphpinator\ConstraintDirectives\Exception\MimeTypeConstraintNotSatisfied();
        }
    }

    protected function specificValidateVariance(
        \Graphpinator\Value\ArgumentValueSet $biggerSet,
        \Graphpinator\Value\ArgumentValueSet $smallerSet,
    ) : void
    {
        $lhs = $biggerSet->getRawValues();
        $rhs = $smallerSet->getRawValues();

        if (\is_int($lhs->maxSize) && ($rhs->maxSize === null || $rhs->maxSize > $lhs->maxSize)) {
            throw new \Exception();
        }

        if (\is_array($lhs->mimeType) && ($rhs->mimeType === null || !self::varianceValidateOneOf($lhs->mimeType, $rhs->mimeType))) {
            throw new \Exception();
        }
    }

    protected static function varianceValidateOneOf(array $greater, array $smaller) : bool
    {
        foreach ($smaller as $value) {
            if (!\in_array($value, $greater, true)) {
                return false;
            }
        }

        return true;
    }
}
