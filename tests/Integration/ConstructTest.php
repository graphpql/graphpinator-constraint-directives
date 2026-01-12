<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor;
use Graphpinator\ConstraintDirectives\FloatConstraintDirective;
use Graphpinator\ConstraintDirectives\IntConstraintDirective;
use Graphpinator\ConstraintDirectives\ListConstraintDirective;
use Graphpinator\ConstraintDirectives\ListConstraintInput;
use Graphpinator\ConstraintDirectives\ObjectConstraintDirective;
use Graphpinator\ConstraintDirectives\ObjectConstraintInput;
use Graphpinator\ConstraintDirectives\StringConstraintDirective;
use Graphpinator\ConstraintDirectives\UploadConstraintDirective;
use Graphpinator\Typesystem\Location\ExecutableDirectiveLocation;
use Graphpinator\Typesystem\Location\TypeSystemDirectiveLocation;
use PHPUnit\Framework\TestCase;

final class ConstructTest extends TestCase
{
    private static ?ConstraintDirectiveAccessor $accessor = null;
    private static ?StringConstraintDirective $stringDirective = null;
    private static ?IntConstraintDirective $intDirective = null;
    private static ?FloatConstraintDirective $floatDirective = null;
    private static ?ListConstraintDirective $listDirective = null;
    private static ?ListConstraintInput $listInput = null;
    private static ?ObjectConstraintInput $objectInput = null;
    private static ?ObjectConstraintDirective $objectDirective = null;
    private static ?UploadConstraintDirective $uploadDirective = null;

    public static function getString() : StringConstraintDirective
    {
        if (!self::$stringDirective instanceof StringConstraintDirective) {
            self::$stringDirective = new StringConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$stringDirective;
    }

    public static function getInt() : IntConstraintDirective
    {
        if (!self::$intDirective instanceof IntConstraintDirective) {
            self::$intDirective = new IntConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$intDirective;
    }

    public static function getFloat() : FloatConstraintDirective
    {
        if (!self::$floatDirective instanceof FloatConstraintDirective) {
            self::$floatDirective = new FloatConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$floatDirective;
    }

    public static function getList() : ListConstraintDirective
    {
        if (!self::$listDirective instanceof ListConstraintDirective) {
            self::$listDirective = new ListConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$listDirective;
    }

    public static function getObject() : ObjectConstraintDirective
    {
        if (!self::$objectDirective instanceof ObjectConstraintDirective) {
            self::$objectDirective = new ObjectConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$objectDirective;
    }

    public static function getListInput() : ListConstraintInput
    {
        if (!self::$listInput instanceof ListConstraintInput) {
            self::$listInput = new ListConstraintInput(
                self::getAccessor(),
            );
        }

        return self::$listInput;
    }

    public static function getObjectInput() : ObjectConstraintInput
    {
        if (!self::$objectInput instanceof ObjectConstraintInput) {
            self::$objectInput = new ObjectConstraintInput(
                self::getAccessor(),
            );
        }

        return self::$objectInput;
    }

    public static function getUpload() : UploadConstraintDirective
    {
        if (!self::$uploadDirective instanceof UploadConstraintDirective) {
            self::$uploadDirective = new UploadConstraintDirective(
                self::getAccessor(),
            );
        }

        return self::$uploadDirective;
    }

    public static function getAccessor() : ConstraintDirectiveAccessor
    {
        if (self::$accessor === null) {
            self::$accessor = new class implements ConstraintDirectiveAccessor
            {
                public function getString() : StringConstraintDirective
                {
                    return ConstructTest::getString();
                }

                public function getInt() : IntConstraintDirective
                {
                    return ConstructTest::getInt();
                }

                public function getFloat() : FloatConstraintDirective
                {
                    return ConstructTest::getFloat();
                }

                public function getList() : ListConstraintDirective
                {
                    return ConstructTest::getList();
                }

                public function getListInput() : ListConstraintInput
                {
                    return ConstructTest::getListInput();
                }

                public function getObject() : ObjectConstraintDirective
                {
                    return ConstructTest::getObject();
                }

                public function getObjectInput() : ObjectConstraintInput
                {
                    return ConstructTest::getObjectInput();
                }

                public function getUpload() : UploadConstraintDirective
                {
                    return ConstructTest::getUpload();
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
        self::assertInstanceOf(StringConstraintDirective::class, self::$stringDirective);
        self::assertInstanceOf(IntConstraintDirective::class, self::$intDirective);
        self::assertInstanceOf(FloatConstraintDirective::class, self::$floatDirective);
        self::assertInstanceOf(ListConstraintDirective::class, self::$listDirective);
        self::assertInstanceOf(ObjectConstraintDirective::class, self::$objectDirective);

        self::assertSame(
            [
                TypeSystemDirectiveLocation::FIELD_DEFINITION,
                TypeSystemDirectiveLocation::ARGUMENT_DEFINITION,
                TypeSystemDirectiveLocation::INPUT_FIELD_DEFINITION,
                ExecutableDirectiveLocation::VARIABLE_DEFINITION,
            ],
            self::getString()->getLocations(),
        );
        self::assertFalse(self::getString()->isRepeatable());

        $count = [1, 1, 0, 0];
        $index = 0;

        foreach (self::getString()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        $count = [0, 0, 0];
        $index = 0;

        foreach (self::getInt()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }

        $count = [0, 0, 0];
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

        $count = [1, 1, 1, 0, 0, 0];
        $index = 0;

        foreach (self::getObject()->getArguments() as $argument) {
            self::assertCount($count[$index], $argument->getDirectiveUsages());
            $index++;
        }
    }
}
