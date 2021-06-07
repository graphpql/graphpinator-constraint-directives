<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

final class UploadConstraintDirective extends \Graphpinator\Typesystem\Directive implements
    \Graphpinator\Typesystem\Location\ArgumentDefinitionLocation,
    \Graphpinator\Typesystem\Location\VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'uploadConstraint';
    protected const DESCRIPTION = 'Graphpinator uploadConstraint directive.';

    public function validateArgumentUsage(
        \Graphpinator\Typesystem\Argument\Argument $argument,
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

    protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
    {
        return new \Graphpinator\Typesystem\Argument\ArgumentSet([
            \Graphpinator\Typesystem\Argument\Argument::create('maxSize', \Graphpinator\Typesystem\Container::Int()),
            \Graphpinator\Typesystem\Argument\Argument::create('mimeType', \Graphpinator\Typesystem\Container::String()->notNull()->list()),
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
        $lhs = (object) $biggerSet->getValuesForResolver();
        $rhs = (object) $smallerSet->getValuesForResolver();

        if (\is_int($lhs->maxSize) && ($rhs->maxSize === null || $rhs->maxSize > $lhs->maxSize)) {
            throw new \Exception();
        }

        if (\is_array($lhs->mimeType) && ($rhs->mimeType === null || !self::varianceValidateOneOf($lhs->mimeType, $rhs->mimeType))) {
            throw new \Exception();
        }
    }
}
