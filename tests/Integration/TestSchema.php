<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class TestSchema
{
    use \Nette\StaticClass;

    private static array $types = [];
    private static ?\Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor $accessor = null;
    private static ?\Graphpinator\Container\Container $container = null;

    public static function getSchema() : \Graphpinator\Type\Schema
    {
        return new \Graphpinator\Type\Schema(
            self::getContainer(),
            self::getQuery(),
        );
    }

    public static function getFullSchema() : \Graphpinator\Type\Schema
    {
        return new \Graphpinator\Type\Schema(
            self::getContainer(),
            self::getQuery(),
            self::getQuery(),
            self::getQuery(),
        );
    }

    public static function getType(string $name) : object
    {
        if (\array_key_exists($name, self::$types)) {
            return self::$types[$name];
        }

        self::$types[$name] = match($name) {
            'Query' => self::getQuery(),
            'ConstraintInput' => self::getConstraintInput(),
            'ExactlyOneInput' => self::getExactlyOneInput(),
            'ConstraintType' => self::getConstraintType(),
            'ListConstraintInput' => new \Graphpinator\ConstraintDirectives\ListConstraintInput(
                self::getAccessor(),
            ),
            'stringConstraint' => new \Graphpinator\ConstraintDirectives\StringConstraintDirective(
                self::getAccessor(),
            ),
            'intConstraint' => new \Graphpinator\ConstraintDirectives\IntConstraintDirective(
                self::getAccessor(),
            ),
            'floatConstraint' => new \Graphpinator\ConstraintDirectives\FloatConstraintDirective(
                self::getAccessor(),
            ),
            'listConstraint' => new \Graphpinator\ConstraintDirectives\ListConstraintDirective(
                self::getAccessor(),
            ),
            'objectConstraint' => new \Graphpinator\ConstraintDirectives\ObjectConstraintDirective(
                self::getAccessor(),
            ),
        };

        return self::$types[$name];
    }

    public static function getAccessor() : \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
    {
        if (self::$accessor === null) {
            self::$accessor = new class implements \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
            {
                public function getString(): \Graphpinator\ConstraintDirectives\StringConstraintDirective
                {
                    return TestSchema::getType('stringConstraint');
                }

                public function getInt(): \Graphpinator\ConstraintDirectives\IntConstraintDirective
                {
                    return TestSchema::getType('intConstraint');
                }

                public function getFloat(): \Graphpinator\ConstraintDirectives\FloatConstraintDirective
                {
                    return TestSchema::getType('floatConstraint');
                }

                public function getList(): \Graphpinator\ConstraintDirectives\ListConstraintDirective
                {
                    return TestSchema::getType('listConstraint');
                }

                public function getListInput(): \Graphpinator\ConstraintDirectives\ListConstraintInput
                {
                    return TestSchema::getType('ListConstraintInput');
                }

                public function getObject(): \Graphpinator\ConstraintDirectives\ObjectConstraintDirective
                {
                    return TestSchema::getType('objectConstraint');
                }
            };
        }

        return self::$accessor;
    }

    public static function getContainer() : \Graphpinator\Container\Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        self::$container = new \Graphpinator\Container\SimpleContainer([
            'Query' => self::getType('Query'),
            'ConstraintInput' => self::getType('ConstraintInput'),
            'ExactlyOneInput' => self::getType('ExactlyOneInput'),
            'ConstraintType' => self::getType('ConstraintType'),
            'ListConstraintInput' => self::getType('ListConstraintInput'),
        ], [
            'stringConstraint' => self::getType('stringConstraint'),
            'intConstraint' => self::getType('intConstraint'),
            'floatConstraint' => self::getType('floatConstraint'),
            'listConstraint' => self::getType('listConstraint'),
            'objectConstraint' => self::getType('objectConstraint'),
        ]);

        return self::$container;
    }

    public static function getQuery() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldInput',
                        \Graphpinator\Container\Container::Int(),
                        static function ($parent, \stdClass $arg) : int {
                            return 1;
                        },
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        new \Graphpinator\Argument\Argument(
                            'arg',
                            TestSchema::getConstraintInput(),
                        ),
                    ])),
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldExactlyOne',
                        \Graphpinator\Container\Container::Int(),
                        static function ($parent, \stdClass $arg) : int {
                            return 1;
                        },
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        new \Graphpinator\Argument\Argument(
                            'arg',
                            TestSchema::getExactlyOneInput(),
                        ),
                    ])),
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldAOrB',
                        TestSchema::getAOrBType()->notNull(),
                        static function ($parent) : int {
                            return 0;
                        },
                    ),
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldList',
                        \Graphpinator\Container\Container::Int()->list(),
                        static function ($parent, array $arg) : array {
                            return $arg;
                        },
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['minItems' => 3, 'maxItems' => 5],
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        new \Graphpinator\Argument\Argument(
                            'arg',
                            \Graphpinator\Container\Container::Int()->list(),
                        ),
                    ]),
                    ),
                ]);
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }
        };
    }

    public static function getConstraintType() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'ConstraintType';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => [
                        'intMinField',
                        'intMaxField',
                        'intOneOfField',
                        'floatMinField',
                        'floatMaxField',
                        'floatOneOfField',
                        'stringMinField',
                        'stringMaxField',
                        'listMinField',
                        'listMaxField',
                    ]],
                );
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    (new \Graphpinator\Field\ResolvableField(
                        'intMinField',
                        \Graphpinator\Container\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new \Graphpinator\Field\ResolvableField(
                        'intMaxField',
                        \Graphpinator\Container\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new \Graphpinator\Field\ResolvableField(
                        'intOneOfField',
                        \Graphpinator\Container\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2 , 3]]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatMinField',
                        \Graphpinator\Container\Container::Float(),
                        static function () {
                            return 4.02;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatMaxField',
                        \Graphpinator\Container\Container::Float(),
                        static function () {
                            return 1.1;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatOneOfField',
                        \Graphpinator\Container\Container::Float(),
                        static function () {
                            return 1.01;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new \Graphpinator\Field\ResolvableField(
                        'stringMinField',
                        \Graphpinator\Container\Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new \Graphpinator\Field\ResolvableField(
                        'stringMaxField',
                        \Graphpinator\Container\Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new \Graphpinator\Field\ResolvableField(
                        'listMinField',
                        \Graphpinator\Container\Container::Int()->list(),
                        static function () : array {
                            return [1];
                        },
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new \Graphpinator\Field\ResolvableField(
                        'listMaxField',
                        \Graphpinator\Container\Container::Int()->list(),
                        static function () : array {
                            return [1, 2];
                        },
                    ))->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => 3]),
                ]);
            }
        };
    }

    public static function getConstraintInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'ConstraintInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['atLeastOne' => [
                        'intMinArg',
                        'intMaxArg',
                        'intOneOfArg',
                        'floatMinArg',
                        'floatMaxArg',
                        'floatOneOfArg',
                        'stringMinArg',
                        'stringMaxArg',
                        'stringRegexArg',
                        'stringOneOfArg',
                        'listMinArg',
                        'listMaxArg',
                        'listUniqueArg',
                        'listInnerListArg',
                        'listMinIntMinArg',
                    ]],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    (new \Graphpinator\Argument\Argument(
                        'intMinArg',
                        \Graphpinator\Container\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new \Graphpinator\Argument\Argument(
                        'intMaxArg',
                        \Graphpinator\Container\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new \Graphpinator\Argument\Argument(
                        'intOneOfArg',
                        \Graphpinator\Container\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2, 3]]),
                    (new \Graphpinator\Argument\Argument(
                        'floatMinArg',
                        \Graphpinator\Container\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new \Graphpinator\Argument\Argument(
                        'floatMaxArg',
                        \Graphpinator\Container\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new \Graphpinator\Argument\Argument(
                        'floatOneOfArg',
                        \Graphpinator\Container\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new \Graphpinator\Argument\Argument(
                        'stringMinArg',
                        \Graphpinator\Container\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new \Graphpinator\Argument\Argument(
                        'stringMaxArg',
                        \Graphpinator\Container\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new \Graphpinator\Argument\Argument(
                        'stringRegexArg',
                        \Graphpinator\Container\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['regex' => '/^(abc)|(foo)$/']),
                    (new \Graphpinator\Argument\Argument(
                        'stringOneOfArg',
                        \Graphpinator\Container\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => ['abc', 'foo']]),
                    (new \Graphpinator\Argument\Argument(
                        'listMinArg',
                        \Graphpinator\Container\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new \Graphpinator\Argument\Argument(
                        'listMaxArg',
                        \Graphpinator\Container\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => 3]),
                    (new \Graphpinator\Argument\Argument(
                        'listUniqueArg',
                        \Graphpinator\Container\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['unique' => true]),
                    (new \Graphpinator\Argument\Argument(
                        'listInnerListArg',
                        \Graphpinator\Container\Container::Int()->list()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['innerList' => (object) [
                        'minItems' => 1,
                        'maxItems' => 3,
                    ]]),
                    \Graphpinator\Argument\Argument::create('listMinIntMinArg', \Graphpinator\Container\Container::Int()->list())
                        ->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 3])
                        ->addDirective(TestSchema::getType('intConstraint'), ['min' => 3]),
                ]);
            }
        };
    }

    public static function getExactlyOneInput() : \Graphpinator\Type\InputType
    {
        return new class extends \Graphpinator\Type\InputType
        {
            protected const NAME = 'ExactlyOneInput';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => ['int1', 'int2']],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    new \Graphpinator\Argument\Argument(
                        'int1',
                        \Graphpinator\Container\Container::Int(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'int2',
                        \Graphpinator\Container\Container::Int(),
                    ),
                ]);
            }
        };
    }

    public static function getAOrBType() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'AOrB';
            protected const DESCRIPTION = 'Graphpinator Constraints: AOrB type';

            public function __construct()
            {
                parent::__construct();

                $this->addDirective(
                    TestSchema::getType('objectConstraint'),
                    ['exactlyOne' => ['fieldA', 'fieldB']],
                );
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return \is_int($rawValue) && \in_array($rawValue, [0, 1], true);
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    new \Graphpinator\Field\ResolvableField(
                        'fieldA',
                        \Graphpinator\Container\Container::Int(),
                        static function (?int $parent) : ?int {
                            return $parent === 1
                                ? 1
                                : null;
                        },
                    ),
                    new \Graphpinator\Field\ResolvableField(
                        'fieldB',
                        \Graphpinator\Container\Container::Int(),
                        static function (int $parent) : ?int {
                            return $parent === 0
                                ? 1
                                : null;
                        },
                    ),
                ]);
            }
        };
    }
}
