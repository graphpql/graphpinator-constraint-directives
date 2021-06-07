<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class ValidateTypeTest extends \PHPUnit\Framework\TestCase
{
    public function testInvalidConstraintTypeString() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Float(),
                    )->addDirective(TestSchema::getType('stringConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeInt() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('intConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeFloat() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Int(),
                    )->addDirective(TestSchema::getType('floatConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeList() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('listConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMinLength() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMaxLength() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMinItems() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['minItems' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMaxItems() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->list()->notNull(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInnerNegativeMinItems() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->list()->notNull(),
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['innerList' => (object) ['minItems' => -20]],
                    ),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInnerNegativeMaxItems() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->list()->notNull(),
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['innerList' => (object) ['maxItems' => -20]],
                    ),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInnerInvalidType() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->list(),
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['innerList' => (object) ['maxItems' => 20]],
                    ),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testEmptyOneOfInt() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfInt() : void
    {
        $this->expectException(\Graphpinator\Exception\Value\InvalidValue::class);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testEmptyOneOfFloat() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfFloat() : void
    {
        $this->expectException(\Graphpinator\Exception\Value\InvalidValue::class);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testEmptyOneOfString() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfString() : void
    {
        $this->expectException(\Graphpinator\Exception\Value\InvalidValue::class);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => [1]]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testUniqueConstraintList() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\UniqueConstraintOnlyScalar::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\UniqueConstraintOnlyScalar::MESSAGE);

        $type = new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    \Graphpinator\Argument\Argument::create(
                        'arg',
                        \Graphpinator\Container\Container::String()->notNullList()->list()->notNull(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['unique' => true]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidAtLeastOneParameter() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::MESSAGE);

        new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => []],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet();
            }
        };
    }

    public function testInvalidAtLeastOneParameter2() : void
    {
        $this->expectException(\Graphpinator\Exception\Value\InvalidValue::class);

        new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => [1]],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet();
            }
        };
    }

    public function testInvalidExactlyOneParameter() : void
    {
        $this->expectException(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(\Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::MESSAGE);

        new class extends \Graphpinator\Type\InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => []],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet();
            }
        };
    }

    public function testInvalidConstraintTypeMissingFieldAtLeastOne() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        new class extends \Graphpinator\Type\InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => ['arg1', 'arg2']],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument(
                        'arg1',
                        \Graphpinator\Container\Container::Int(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'arg3',
                        \Graphpinator\Container\Container::Int(),
                    ),
                ]);
            }
        };
    }

    public function testInvalidConstraintTypeMissingFieldExactlyOne() : void
    {
        $this->expectException(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::class);
        $this->expectExceptionMessage(\Graphpinator\Typesystem\Exception\DirectiveIncorrectType::MESSAGE);

        new class extends \Graphpinator\Type\InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => ['arg1', 'arg2']],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument(
                        'arg1',
                        \Graphpinator\Container\Container::Int(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'arg3',
                        \Graphpinator\Container\Container::Int(),
                    ),
                ]);
            }
        };
    }
}
