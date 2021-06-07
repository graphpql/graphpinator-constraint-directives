<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class VarianceTest extends \PHPUnit\Framework\TestCase
{
    public function testMissingBiggerSet() : void
    {
        $directive = TestSchema::getType('stringConstraint');
        self::assertInstanceOf(\Graphpinator\Typesystem\Directive::class, $directive);
        self::assertInstanceOf(\Graphpinator\Typesystem\Location\FieldDefinitionLocation::class, $directive);
        self::assertInstanceOf(\Graphpinator\Typesystem\Location\ArgumentDefinitionLocation::class, $directive);

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

    public function testMissingBiggerSetUpload() : void
    {
        $directive = TestSchema::getType('uploadConstraint');
        self::assertInstanceOf(\Graphpinator\Directive\Directive::class, $directive);
        self::assertInstanceOf(\Graphpinator\Typesystem\Location\ArgumentDefinitionLocation::class, $directive);

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

    public function testMissingSmallerSetUpload() : void
    {
        $this->expectException(\Throwable::class);

        $directive = TestSchema::getType('uploadConstraint');
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
