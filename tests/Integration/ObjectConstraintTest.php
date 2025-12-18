<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\Common\Path;
use Graphpinator\ConstraintDirectives\Exception\AtLeastConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\AtMostConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\ExactlyConstraintNotSatisfied;
use Graphpinator\Normalizer\VariableValueSet;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Value\InputValue;
use Graphpinator\Value\Visitor\ConvertRawValueVisitor;
use PHPUnit\Framework\TestCase;

final class ObjectConstraintTest extends TestCase
{
    public function testAtLeastValidExactly() : void
    {
        $value = self::getAtLeastInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value'], new Path()),
        );
        \assert($value instanceof InputValue);
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testAtLeastValidMore() : void
    {
        $value = self::getAtLeastInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testAtLeastInvalid() : void
    {
        $this->expectException(AtLeastConstraintNotSatisfied::class);

        $value = self::getAtLeastInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => null, 'arg2' => null], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));
    }

    public function testAtMostValidExactly() : void
    {
        $value = self::getAtMostInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value'], new Path()),
        );
        \assert($value instanceof InputValue);
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testAtMostValidLess() : void
    {
        $value = self::getAtMostInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => null, 'arg2' => null], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testAtMostInvalid() : void
    {
        $this->expectException(AtMostConstraintNotSatisfied::class);

        $value = self::getAtMostInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));
    }

    public function testExactlyValidEmpty() : void
    {
        $value = self::getExactlyInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value'], new Path()),
        );
        \assert($value instanceof InputValue);
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testExactlyValidNull() : void
    {
        $value = self::getExactlyInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => null], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));

        self::assertInstanceOf(InputValue::class, $value);
    }

    public function testExactlyInvalid() : void
    {
        $this->expectException(ExactlyConstraintNotSatisfied::class);

        $value = self::getExactlyInput()->accept(
            new ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new Path()),
        );
        $value->applyVariables(new VariableValueSet([]));
    }

    private static function getAtLeastInput() : InputType
    {
        return new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['atLeast' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'arg1',
                        Container::String(),
                    ),
                    new Argument(
                        'arg2',
                        Container::String(),
                    ),
                ]);
            }
        };
    }

    private static function getAtMostInput() : InputType
    {
        return new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['atMost' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'arg1',
                        Container::String(),
                    ),
                    new Argument(
                        'arg2',
                        Container::String(),
                    ),
                ]);
            }
        };
    }

    private static function getExactlyInput() : InputType
    {
        return new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['exactly' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'arg1',
                        Container::String(),
                    ),
                    new Argument(
                        'arg2',
                        Container::String(),
                    ),
                ]);
            }
        };
    }
}
