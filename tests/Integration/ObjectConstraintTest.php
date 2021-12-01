<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class ObjectConstraintTest extends \PHPUnit\Framework\TestCase
{
    public function testAtLeastValidExactly() : void
    {
        $value = self::getAtLeastInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value'], new \Graphpinator\Common\Path()),
        );
        \assert($value instanceof \Graphpinator\Value\InputValue);
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testAtLeastValidMore() : void
    {
        $value = self::getAtLeastInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testAtLeastInvalid() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\AtLeastConstraintNotSatisfied::class);

        $value = self::getAtLeastInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => null, 'arg2' => null], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));
    }

    public function testAtMostValidExactly() : void
    {
        $value = self::getAtMostInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value'], new \Graphpinator\Common\Path()),
        );
        \assert($value instanceof \Graphpinator\Value\InputValue);
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testAtMostValidLess() : void
    {
        $value = self::getAtMostInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => null, 'arg2' => null], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testAtMostInvalid() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\AtMostConstraintNotSatisfied::class);

        $value = self::getAtMostInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));
    }

    public function testExactlyValidEmpty() : void
    {
        $value = self::getExactlyInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value'], new \Graphpinator\Common\Path()),
        );
        \assert($value instanceof \Graphpinator\Value\InputValue);
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testExactlyValidNull() : void
    {
        $value = self::getExactlyInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => null], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));

        self::assertInstanceOf(\Graphpinator\Value\InputValue::class, $value);
    }

    public function testExactlyInvalid() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\ExactlyConstraintNotSatisfied::class);

        $value = self::getExactlyInput()->accept(
            new \Graphpinator\Value\ConvertRawValueVisitor((object) ['arg1' => 'Value', 'arg2' => 'Value'], new \Graphpinator\Common\Path()),
        );
        $value->applyVariables(new \Graphpinator\Normalizer\VariableValueSet([]));
    }

    private static function getAtLeastInput() : \Graphpinator\Typesystem\InputType
    {
        return new class extends \Graphpinator\Typesystem\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeast' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
            {
                return new \Graphpinator\Typesystem\Argument\ArgumentSet([
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg1',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg2',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                ]);
            }
        };
    }

    private static function getAtMostInput() : \Graphpinator\Typesystem\InputType
    {
        return new class extends \Graphpinator\Typesystem\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atMost' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
            {
                return new \Graphpinator\Typesystem\Argument\ArgumentSet([
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg1',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg2',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                ]);
            }
        };
    }

    private static function getExactlyInput() : \Graphpinator\Typesystem\InputType
    {
        return new class extends \Graphpinator\Typesystem\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactly' => (object) ['count' => 1, 'from' => ['arg1', 'arg2']]],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Argument\ArgumentSet
            {
                return new \Graphpinator\Typesystem\Argument\ArgumentSet([
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg1',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                    new \Graphpinator\Typesystem\Argument\Argument(
                        'arg2',
                        \Graphpinator\Typesystem\Container::String(),
                    ),
                ]);
            }
        };
    }
}
