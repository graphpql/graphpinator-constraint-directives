<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class TestSchema
{
    use \Nette\StaticClass;

    private static array $types = [];
    private static ?\Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor $accessor = null;
    private static ?\Graphpinator\Typesystem\Container $container = null;

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

        self::$types[$name] = match ($name) {
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
            'uploadConstraint' => new \Graphpinator\ConstraintDirectives\UploadConstraintDirective(
                self::getAccessor(),
            )
        };

        return self::$types[$name];
    }

    public static function getAccessor() : \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
    {
        if (self::$accessor === null) {
            self::$accessor = new class implements \Graphpinator\ConstraintDirectives\ConstraintDirectiveAccessor
            {
                public function getString() : \Graphpinator\ConstraintDirectives\StringConstraintDirective
                {
                    return TestSchema::getType('stringConstraint');
                }

                public function getInt() : \Graphpinator\ConstraintDirectives\IntConstraintDirective
                {
                    return TestSchema::getType('intConstraint');
                }

                public function getFloat() : \Graphpinator\ConstraintDirectives\FloatConstraintDirective
                {
                    return TestSchema::getType('floatConstraint');
                }

                public function getList() : \Graphpinator\ConstraintDirectives\ListConstraintDirective
                {
                    return TestSchema::getType('listConstraint');
                }

                public function getListInput() : \Graphpinator\ConstraintDirectives\ListConstraintInput
                {
                    return TestSchema::getType('ListConstraintInput');
                }

                public function getObject() : \Graphpinator\ConstraintDirectives\ObjectConstraintDirective
                {
                    return TestSchema::getType('objectConstraint');
                }

                public function getUpload() : \Graphpinator\ConstraintDirectives\UploadConstraintDirective
                {
                    return TestSchema::getType('uploadConstraint');
                }
            };
        }

        return self::$accessor;
    }

    public static function getContainer() : \Graphpinator\Typesystem\Container
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
            'uploadConstraint' => self::getType('uploadConstraint'),
        ]);

        return self::$container;
    }

    public static function getQuery() : \Graphpinator\Type\Type
    {
        return new class extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldInput',
                        \Graphpinator\Typesystem\Container::Int(),
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
                        \Graphpinator\Typesystem\Container::Int(),
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
                        \Graphpinator\Typesystem\Container::Int()->list(),
                        static function ($parent, array $arg) : array {
                            return $arg;
                        },
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['minItems' => 3, 'maxItems' => 5],
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        new \Graphpinator\Argument\Argument(
                            'arg',
                            \Graphpinator\Typesystem\Container::Int()->list(),
                        ),
                    ])),
                ]);
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
                    [
                        'atLeastOne' => [
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
                        ],
                    ],
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
                        \Graphpinator\Typesystem\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new \Graphpinator\Field\ResolvableField(
                        'intMaxField',
                        \Graphpinator\Typesystem\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new \Graphpinator\Field\ResolvableField(
                        'intOneOfField',
                        \Graphpinator\Typesystem\Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2, 3]]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatMinField',
                        \Graphpinator\Typesystem\Container::Float(),
                        static function () {
                            return 4.02;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatMaxField',
                        \Graphpinator\Typesystem\Container::Float(),
                        static function () {
                            return 1.1;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new \Graphpinator\Field\ResolvableField(
                        'floatOneOfField',
                        \Graphpinator\Typesystem\Container::Float(),
                        static function () {
                            return 1.01;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new \Graphpinator\Field\ResolvableField(
                        'stringMinField',
                        \Graphpinator\Typesystem\Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new \Graphpinator\Field\ResolvableField(
                        'stringMaxField',
                        \Graphpinator\Typesystem\Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new \Graphpinator\Field\ResolvableField(
                        'listMinField',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                        static function () : array {
                            return [1];
                        },
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new \Graphpinator\Field\ResolvableField(
                        'listMaxField',
                        \Graphpinator\Typesystem\Container::Int()->list(),
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
                    [
                        'atLeastOne' => [
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
                        ],
                    ],
                );
            }

            protected function getFieldDefinition() : \Graphpinator\Argument\ArgumentSet
            {
                return new \Graphpinator\Argument\ArgumentSet([
                    (new \Graphpinator\Argument\Argument(
                        'intMinArg',
                        \Graphpinator\Typesystem\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new \Graphpinator\Argument\Argument(
                        'intMaxArg',
                        \Graphpinator\Typesystem\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new \Graphpinator\Argument\Argument(
                        'intOneOfArg',
                        \Graphpinator\Typesystem\Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2, 3]]),
                    (new \Graphpinator\Argument\Argument(
                        'floatMinArg',
                        \Graphpinator\Typesystem\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new \Graphpinator\Argument\Argument(
                        'floatMaxArg',
                        \Graphpinator\Typesystem\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new \Graphpinator\Argument\Argument(
                        'floatOneOfArg',
                        \Graphpinator\Typesystem\Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new \Graphpinator\Argument\Argument(
                        'stringMinArg',
                        \Graphpinator\Typesystem\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new \Graphpinator\Argument\Argument(
                        'stringMaxArg',
                        \Graphpinator\Typesystem\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new \Graphpinator\Argument\Argument(
                        'stringRegexArg',
                        \Graphpinator\Typesystem\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['regex' => '/^(abc)|(foo)$/']),
                    (new \Graphpinator\Argument\Argument(
                        'stringOneOfArg',
                        \Graphpinator\Typesystem\Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => ['abc', 'foo']]),
                    (new \Graphpinator\Argument\Argument(
                        'listMinArg',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new \Graphpinator\Argument\Argument(
                        'listMaxArg',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => 3]),
                    (new \Graphpinator\Argument\Argument(
                        'listUniqueArg',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['unique' => true]),
                    (new \Graphpinator\Argument\Argument(
                        'listInnerListArg',
                        \Graphpinator\Typesystem\Container::Int()->list()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), [
                        'innerList' => (object) [
                            'minItems' => 1,
                            'maxItems' => 3,
                        ],
                    ]),
                    \Graphpinator\Argument\Argument::create('listMinIntMinArg', \Graphpinator\Typesystem\Container::Int()->list())
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
                        \Graphpinator\Typesystem\Container::Int(),
                    ),
                    new \Graphpinator\Argument\Argument(
                        'int2',
                        \Graphpinator\Typesystem\Container::Int(),
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
                        \Graphpinator\Typesystem\Container::Int(),
                        static function (?int $parent) : ?int {
                            return $parent === 1
                                ? 1
                                : null;
                        },
                    ),
                    new \Graphpinator\Field\ResolvableField(
                        'fieldB',
                        \Graphpinator\Typesystem\Container::Int(),
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
