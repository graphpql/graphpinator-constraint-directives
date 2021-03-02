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

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive->getArguments());

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSet() : void
    {
        $this->expectException(\Exception::class);

        $directive = TestSchema::getType('stringConstraint');
        \assert($directive instanceof \Graphpinator\Directive\Directive);

        $values = \Graphpinator\Value\ArgumentValueSet::fromRaw([], $directive->getArguments());

        $directive->validateVariance($values, null);
    }
}
