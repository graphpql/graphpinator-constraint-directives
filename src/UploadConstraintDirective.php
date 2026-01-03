<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives;

use Graphpinator\ConstraintDirectives\Exception\MaxSizeConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MimeTypeConstraintNotSatisfied;
use Graphpinator\Normalizer\Variable\Variable;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Attribute\Description;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Directive;
use Graphpinator\Typesystem\Location\ArgumentDefinitionLocation;
use Graphpinator\Typesystem\Location\VariableDefinitionLocation;
use Graphpinator\Typesystem\Visitor\GetNamedTypeVisitor;
use Graphpinator\Upload\UploadType;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Contract\Value;

#[Description('Graphpinator uploadConstraint directive.')]
final class UploadConstraintDirective extends Directive implements
    ArgumentDefinitionLocation,
    VariableDefinitionLocation
{
    use TScalarConstraint;

    protected const NAME = 'uploadConstraint';

    #[\Override]
    public function validateArgumentUsage(Argument $argument, ArgumentValueSet $arguments) : bool
    {
        return $argument->getType()->accept(new GetNamedTypeVisitor()) instanceof UploadType;
    }

    #[\Override]
    public function validateVariableUsage(Variable $variable, ArgumentValueSet $arguments) : bool
    {
        return $variable->type->accept(new GetNamedTypeVisitor()) instanceof UploadType;
    }

    #[\Override]
    protected function getFieldDefinition() : ArgumentSet
    {
        return new ArgumentSet([
            Argument::create('maxSize', Container::Int()),
            Argument::create('mimeType', Container::String()->notNull()->list()),
        ]);
    }

    #[\Override]
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

    #[\Override]
    protected function specificValidateValue(Value $value, ArgumentValueSet $arguments) : void
    {
        $rawValue = $value->getRawValue();
        $maxSize = $arguments->offsetGet('maxSize')->value->getRawValue();
        $mimeType = $arguments->offsetGet('mimeType')->value->getRawValue();

        if (\is_int($maxSize) && $rawValue->getSize() > $maxSize) {
            throw new MaxSizeConstraintNotSatisfied();
        }

        // @phpstan-ignore theCodingMachineSafe.function
        if (\is_array($mimeType) && !\in_array(\mime_content_type($rawValue->getStream()->getMetadata('uri')), $mimeType, true)) {
            throw new MimeTypeConstraintNotSatisfied();
        }
    }

    #[\Override]
    protected function specificValidateVariance(ArgumentValueSet $biggerSet, ArgumentValueSet $smallerSet) : void
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
