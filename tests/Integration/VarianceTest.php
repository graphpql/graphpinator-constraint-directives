<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\Common\Path;
use Graphpinator\ConstraintDirectives\StringConstraintDirective;
use Graphpinator\ConstraintDirectives\UploadConstraintDirective;
use Graphpinator\Value\ArgumentValueSet;
use Graphpinator\Value\Visitor\ConvertRawValueVisitor;
use PHPUnit\Framework\TestCase;

final class VarianceTest extends TestCase
{
    public function testMissingBiggerSet() : void
    {
        $directive = TestSchema::$stringConstraint;
        self::assertInstanceOf(StringConstraintDirective::class, $directive);
        \assert($directive instanceof StringConstraintDirective);

        $values = new ArgumentValueSet(
            (array) ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new Path(),
            ),
        );

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSet() : void
    {
        $this->expectException(\Throwable::class);

        $directive = TestSchema::$stringConstraint;
        self::assertInstanceOf(StringConstraintDirective::class, $directive);
        \assert($directive instanceof StringConstraintDirective);

        $values = new ArgumentValueSet(
            (array) ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new Path(),
            ),
        );

        $directive->validateVariance($values, null);
    }

    public function testMissingBiggerSetUpload() : void
    {
        $directive = TestSchema::$uploadConstraint;
        self::assertInstanceOf(UploadConstraintDirective::class, $directive);
        \assert($directive instanceof UploadConstraintDirective);

        $values = new ArgumentValueSet(
            (array) ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new Path(),
            ),
        );

        $directive->validateVariance(null, $values);
    }

    public function testMissingSmallerSetUpload() : void
    {
        $this->expectException(\Throwable::class);

        $directive = TestSchema::$uploadConstraint;
        self::assertInstanceOf(UploadConstraintDirective::class, $directive);
        \assert($directive instanceof UploadConstraintDirective);

        $values = new ArgumentValueSet(
            (array) ConvertRawValueVisitor::convertArgumentSet(
                $directive->getArguments(),
                new \stdClass(),
                new Path(),
            ),
        );

        $directive->validateVariance($values, null);
    }
}
