<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class UploadConstraintDirective extends \Graphpinator\Directive\Directive implements
    \Graphpinator\Directive\Contract\ArgumentDefinitionLocation,
    \Graphpinator\Directive\Contract\VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'uploadConstraint';
    protected const DESCRIPTION = 'Graphpinator uploadConstraint directive.';

    public function validateArgumentUsage(
        \Graphpinator\Argument\Argument $argument,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $argument->getType()->getNamedType() instanceof \Graphpinator\Upload\UploadType;
    }

    public function validateVariableUsage(
        \Graphpinator\Normalizer\Variable\Variable $variable,
        \Graphpinator\Value\ArgumentValueSet $arguments,
    ) : bool
    {
        return $variable->getType()->getNamedType() instanceof \Graphpinator\Upload\UploadType;
    }

    protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
    {
        return new \Graphpinator\Argument\ArgumentSet([
            \Graphpinator\Argument\Argument::create('maxSize', \Graphpinator\Container\Container::Int()),
            \Graphpinator\Argument\Argument::create('mimeType', \Graphpinator\Container\Container::String()->notNull()->list()),
        ]);
    }

    protected function afterGetFieldDefinition() : void
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
}
