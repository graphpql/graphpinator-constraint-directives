<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class ConstructTest extends \PHPUnit\Framework\TestCase
{
    private static ?\Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor $accessor = null;
    private static ?\Graphpinator\ConstraintDirectives\StringConstraintDirective $stringDirective = null;
    private static ?\Graphpinator\ConstraintDirectives\IntConstraintDirective $intDirective = null;
    private static ?\Graphpinator\ConstraintDirectives\FloatConstraintDirective $floatDirective = null;
    private static ?\Graphpinator\ConstraintDirectives\ListConstraintDirective $listDirective = null;
    private static ?\Graphpinator\ConstraintDirectives\ListConstraintInput $listInput = null;
    private static ?\Graphpinator\ConstraintDirectives\ObjectConstraintDirective $objectDirective = null;

    public static function getString() : \Graphpinator\ConstraintDirectives\StringConstraintDirective
    {
        if (!self::$stringDirective instanceof \Graphpinator\ConstraintDirectives\StringConstraintDirective) {
            self::$stringDirective = new \Graphpinator\ConstraintDirectives\StringConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$stringDirective;
    }

    public static function getInt() : \Graphpinator\ConstraintDirectives\IntConstraintDirective
    {
        if (!self::$intDirective instanceof \Graphpinator\ConstraintDirectives\IntConstraintDirective) {
            self::$intDirective = new \Graphpinator\ConstraintDirectives\IntConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$intDirective;
    }

    public static function getFloat() : \Graphpinator\ConstraintDirectives\FloatConstraintDirective
    {
        if (!self::$floatDirective instanceof \Graphpinator\ConstraintDirectives\FloatConstraintDirective) {
            self::$floatDirective = new \Graphpinator\ConstraintDirectives\FloatConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$floatDirective;
    }

    public static function getList() : \Graphpinator\ConstraintDirectives\ListConstraintDirective
    {
        if (!self::$listDirective instanceof \Graphpinator\ConstraintDirectives\ListConstraintDirective) {
            self::$listDirective = new \Graphpinator\ConstraintDirectives\ListConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$listDirective;
    }

    public static function getObject() : \Graphpinator\ConstraintDirectives\ObjectConstraintDirective
    {
        if (!self::$objectDirective instanceof \Graphpinator\ConstraintDirectives\ObjectConstraintDirective) {
            self::$objectDirective = new \Graphpinator\ConstraintDirectives\ObjectConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$objectDirective;
    }

    public static function getListInput() : \Graphpinator\ConstraintDirectives\ListConstraintInput
    {
        if (!self::$listInput instanceof \Graphpinator\ConstraintDirectives\ListConstraintInput) {
            self::$listInput = new \Graphpinator\ConstraintDirectives\ListConstraintInput(
                self::getAccessor(),
            );
        }

        return self::$listInput;
    }

    public static function getAccessor() : \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
    {
        if (self::$accessor === null) {
            self::$accessor = new class implements \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
            {
                public function getString() : \Graphpinator\ConstraintDirectives\StringConstraintDirective
                {
                    return ConstructTest::getString();
                }

                public function getInt() : \Graphpinator\ConstraintDirectives\IntConstraintDirective
                {
                    return ConstructTest::getInt();
                }

                public function getFloat() : \Graphpinator\ConstraintDirectives\FloatConstraintDirective
                {
                    return ConstructTest::getFloat();
                }

                public function getList() : \Graphpinator\ConstraintDirectives\ListConstraintDirective
                {
                    return ConstructTest::getList();
                }

                public function getListInput() : \Graphpinator\ConstraintDirectives\ListConstraintInput
                {
                    return ConstructTest::getListInput();
                }

                public function getObject() : \Graphpinator\ConstraintDirectives\ObjectConstraintDirective
                {
                    return ConstructTest::getObject();
                }
            };
        }

        return self::$accessor;
    }

    public function testConstruct() : void
    {
        self::getString();
        self::getInt();
        self::getFloat();
        self::getList();
        self::getObject();
        self::assertInstanceOf(\Graphpinator\ConstraintDirectives\StringConstraintDirective::class, self::$stringDirective);
        self::assertInstanceOf(\Graphpinator\ConstraintDirectives\IntConstraintDirective::class, self::$intDirective);
        self::assertInstanceOf(\Graphpinator\ConstraintDirectives\FloatConstraintDirective::class, self::$floatDirective);
        self::assertInstanceOf(\Graphpinator\ConstraintDirectives\ListConstraintDirective::class, self::$listDirective);
        self::assertInstanceOf(\Graphpinator\ConstraintDirectives\ObjectConstraintDirective::class, self::$objectDirective);

        self::assertSame(
            [
                \Graphpinator\Directive\TypeSystemDirectiveLocation::FIELD_DEFINITION,
                \Graphpinator\Directive\TypeSystemDirectiveLocation::ARGUMENT_DEFINITION,
                \Graphpinator\Directive\TypeSystemDirectiveLocation::INPUT_FIELD_DEFINITION,
                \Graphpinator\Directive\ExecutableDirectiveLocation::VARIABLE_DEFINITION,
            ],
            self::getString()->getLocations(),
        );
        self::assertFalse(self::getString()->isRepeatable());

        $count = [1, 1, 0, 1];
        $index = 0;

        foreach (self::getString()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        $count = [0, 0, 1];
        $index = 0;

        foreach (self::getInt()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        $count = [0, 0, 1];
        $index = 0;

        foreach (self::getFloat()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        $count = [1, 1, 0, 0, 0];
        $index = 0;

        foreach (self::getList()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        foreach (self::getObject()->getArguments() as $argument) {
            self::assertCount(1, $argument->getDirectiveUsages());
        }
    }
}
