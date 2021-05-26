<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class VarianceTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingBiggerSet() : void
    {
        $directive = TestSchema::getType('stringConstraint');
        self::assertInstanceOf(\Graphpinator\Directive\Directive::class, $directive);
        self::assertInstanceOf(\Graphpinator\Directive\Contract\FieldDefinitionLocation::class, $directive);
        self::assertInstanceOf(\Graphpinator\Directive\Contract\ArgumentDefinitionLocation::class, $directive);

        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = new \Graphpinator\Value\ArgumentValueSet(
            (array) \Graphpinator\Value\ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new \Graphpinator\Common\Path(),
            ),
        );

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSet() : void
    {
        $this->expectException(\Throwable::class);

        $directive = TestSchema::getType('stringConstraint');
        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = new \Graphpinator\Value\ArgumentValueSet(
            (array) \Graphpinator\Value\ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new \Graphpinator\Common\Path(),
            ),
        );

        $directive->validateVariance($values, null);
    }
}
