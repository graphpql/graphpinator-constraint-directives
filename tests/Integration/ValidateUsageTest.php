<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\UniqueConstraintOnlyScalar;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Exception\DirectiveIncorrectType;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Value\Exception\InvalidValue;
use PHPUnit\Framework\TestCase;

final class ValidateUsageTest extends TestCase
{
    public function testInvalidConstraintTypeString() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Float(),
                    )->addDirective(TestSchema::getType('stringConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeInt() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('intConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeFloat() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Int(),
                    )->addDirective(TestSchema::getType('floatConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidConstraintTypeList() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('listConstraint'), []),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMinLength() : void
    {
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMaxLength() : void
    {
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMinItems() : void
    {
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['minItems' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testNegativeMaxItems() : void
    {
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->list()->notNull(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => -20]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInnerNegativeMinItems() : void
    {
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->list()->notNull(),
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
        $this->expectException(MinConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->list()->notNull(),
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
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->list(),
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
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfInt() : void
    {
        $this->expectException(\Graphpinator\Value\Exception\InvalidValue::class);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testEmptyOneOfFloat() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfFloat() : void
    {
        $this->expectException(InvalidValue::class);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testEmptyOneOfString() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => []]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidOneOfString() : void
    {
        $this->expectException(InvalidValue::class);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => [1]]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testUniqueConstraintList() : void
    {
        $this->expectException(UniqueConstraintOnlyScalar::class);
        $this->expectExceptionMessage(UniqueConstraintOnlyScalar::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::String()->notNullList()->list()->notNull(),
                    )->addDirective(TestSchema::getType('listConstraint'), ['unique' => true]),
                ]);
            }
        };

        $type->getArguments();
    }

    public function testInvalidAtLeastOneParameter() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => []],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };
    }

    public function testInvalidAtLeastOneParameter2() : void
    {
        $this->expectException(InvalidValue::class);

        new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => [1]],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };
    }

    public function testInvalidExactlyOneParameter() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => []],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };
    }

    public function testInvalidConstraintTypeMissingFieldAtLeastOne() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        new class extends InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => ['arg1', 'arg2']],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'arg1',
                        Container::Int(),
                    ),
                    new Argument(
                        'arg3',
                        Container::Int(),
                    ),
                ]);
            }
        };
    }

    public function testInvalidConstraintTypeMissingFieldExactlyOne() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        new class extends InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => ['arg1', 'arg2']],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'arg1',
                        Container::Int(),
                    ),
                    new Argument(
                        'arg3',
                        Container::Int(),
                    ),
                ]);
            }
        };
    }
}
