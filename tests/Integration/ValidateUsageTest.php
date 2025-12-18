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
use Graphpinator\Typesystem\Visitor\ValidateIntegrityVisitor;
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
                    )->addDirective(TestSchema::$stringConstraint, []),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$intConstraint, []),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$floatConstraint, []),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$listConstraint, []),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$stringConstraint, ['minLength' => -20]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$stringConstraint, ['maxLength' => -20]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$listConstraint, ['minItems' => -20]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$listConstraint, ['maxItems' => -20]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                        TestSchema::$listConstraint,
                        ['innerList' => (object) ['minItems' => -20]],
                    ),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                        TestSchema::$listConstraint,
                        ['innerList' => (object) ['maxItems' => -20]],
                    ),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                        TestSchema::$listConstraint,
                        ['innerList' => (object) ['maxItems' => 20]],
                    ),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$intConstraint, ['oneOf' => []]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidOneOfInt() : void
    {
        $this->expectException(InvalidValue::class);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    Argument::create(
                        'arg',
                        Container::Int(),
                    )->addDirective(TestSchema::$intConstraint, ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$floatConstraint, ['oneOf' => []]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$floatConstraint, ['oneOf' => ['string']]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$stringConstraint, ['oneOf' => []]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$stringConstraint, ['oneOf' => [1]]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
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
                    )->addDirective(TestSchema::$listConstraint, ['unique' => true]),
                ]);
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidAtLeastOneParameter() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['atLeastOne' => []],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidAtLeastOneParameter2() : void
    {
        $this->expectException(InvalidValue::class);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['atLeastOne' => [1]],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidExactlyOneParameter() : void
    {
        $this->expectException(MinItemsConstraintNotSatisfied::class);
        $this->expectExceptionMessage(MinItemsConstraintNotSatisfied::MESSAGE);

        $type = new class extends InputType {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
                    ['exactlyOne' => []],
                );
            }

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet();
            }
        };

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidConstraintTypeMissingFieldAtLeastOne() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
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

        $type->accept(new ValidateIntegrityVisitor());
    }

    public function testInvalidConstraintTypeMissingFieldExactlyOne() : void
    {
        $this->expectException(DirectiveIncorrectType::class);
        $this->expectExceptionMessage(DirectiveIncorrectType::MESSAGE);

        $type = new class extends InputType {
            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::$objectConstraint,
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

        $type->accept(new ValidateIntegrityVisitor());
    }
}
