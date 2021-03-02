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

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive);

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSet() : void
    {
        $this->expectException(\Exception::class);

        $directive = TestSchema::getType('stringConstraint');
        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive);

        $directive->validateVariance($values, null);
    }

    public function testMissingBiggerSetUpload() : void
    {
        $directive = TestSchema::getType('uploadConstraint');
        self::assertInstanceOf(\Graphpinator\Directive\Directive::class, $directive);
        self::assertInstanceOf(\Graphpinator\Directive\Contract\ArgumentDefinitionLocation::class, $directive);

        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive);

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSetUpload() : void
    {
        $this->expectException(\Exception::class);

        $directive = TestSchema::getType('uploadConstraint');
        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive);

        $directive->validateVariance($values, null);
    }
}