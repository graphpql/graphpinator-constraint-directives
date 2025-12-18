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
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\InputType;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;

final class TestSchema
{
    private static array $types = [];
    private static ?ConstraintDirectiveAccessor $accessor = null;
    private static ?Container $container = null;

    public static function getSchema() : Schema
    {
        return new Schema(
            self::getContainer(),
            self::getQuery(),
        );
    }

    public static function getFullSchema() : Schema
    {
        return new Schema(
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
            'ListConstraintInput' => new ListConstraintInput(
                self::getAccessor(),
            ),
            'ObjectConstraintInput' => new ObjectConstraintInput(
                self::getAccessor(),
            ),
            'stringConstraint' => new StringConstraintDirective(
                self::getAccessor(),
            ),
            'intConstraint' => new IntConstraintDirective(
                self::getAccessor(),
            ),
            'floatConstraint' => new FloatConstraintDirective(
                self::getAccessor(),
            ),
            'listConstraint' => new ListConstraintDirective(
                self::getAccessor(),
            ),
            'objectConstraint' => new ObjectConstraintDirective(
                self::getAccessor(),
            ),
            'uploadConstraint' => new UploadConstraintDirective(
                self::getAccessor(),
            )
        };

        return self::$types[$name];
    }

    public static function getAccessor() : ConstraintDirectiveAccessor
    {
        if (self::$accessor === null) {
            self::$accessor = new class implements ConstraintDirectiveAccessor
            {
                public function getString() : StringConstraintDirective
                {
                    return TestSchema::getType('stringConstraint');
                }

                public function getInt() : IntConstraintDirective
                {
                    return TestSchema::getType('intConstraint');
                }

                public function getFloat() : FloatConstraintDirective
                {
                    return TestSchema::getType('floatConstraint');
                }

                public function getList() : ListConstraintDirective
                {
                    return TestSchema::getType('listConstraint');
                }

                public function getListInput() : ListConstraintInput
                {
                    return TestSchema::getType('ListConstraintInput');
                }

                public function getObject() : ObjectConstraintDirective
                {
                    return TestSchema::getType('objectConstraint');
                }

                public function getObjectInput() : ObjectConstraintInput
                {
                    return TestSchema::getType('ObjectConstraintInput');
                }

                public function getUpload() : UploadConstraintDirective
                {
                    return TestSchema::getType('uploadConstraint');
                }
            };
        }

        return self::$accessor;
    }

    public static function getContainer() : Container
    {
        if (self::$container !== null) {
            return self::$container;
        }

        self::$container = new SimpleContainer([
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

    public static function getQuery() : Type
    {
        return new class extends Type
        {
            protected const NAME = 'Query';

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'fieldInput',
                        Container::Int(),
                        static function ($parent, \stdClass $arg) : int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'arg',
                            TestSchema::getConstraintInput(),
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldExactlyOne',
                        Container::Int(),
                        static function ($parent, \stdClass $arg) : int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'arg',
                            TestSchema::getExactlyOneInput(),
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldAOrB',
                        TestSchema::getAOrBType()->notNull(),
                        static function ($parent) : int {
                            return 0;
                        },
                    ),
                    ResolvableField::create(
                        'fieldList',
                        Container::Int()->list(),
                        static function ($parent, array $arg) : array {
                            return $arg;
                        },
                    )->addDirective(
                        TestSchema::getType('listConstraint'),
                        ['minItems' => 3, 'maxItems' => 5],
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'arg',
                            Container::Int()->list(),
                        ),
                    ])),
                ]);
            }
        };
    }

    public static function getConstraintType() : Type
    {
        return new class extends Type
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

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    (new ResolvableField(
                        'intMinField',
                        Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new ResolvableField(
                        'intMaxField',
                        Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new ResolvableField(
                        'intOneOfField',
                        Container::Int(),
                        static function () : int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2, 3]]),
                    (new ResolvableField(
                        'floatMinField',
                        Container::Float(),
                        static function () {
                            return 4.02;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new ResolvableField(
                        'floatMaxField',
                        Container::Float(),
                        static function () {
                            return 1.1;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new ResolvableField(
                        'floatOneOfField',
                        Container::Float(),
                        static function () {
                            return 1.01;
                        },
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new ResolvableField(
                        'stringMinField',
                        Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new ResolvableField(
                        'stringMaxField',
                        Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new ResolvableField(
                        'listMinField',
                        Container::Int()->list(),
                        static function () : array {
                            return [1];
                        },
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new ResolvableField(
                        'listMaxField',
                        Container::Int()->list(),
                        static function () : array {
                            return [1, 2];
                        },
                    ))->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => 3]),
                ]);
            }
        };
    }

    public static function getConstraintInput() : InputType
    {
        return new class extends InputType
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

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    (new Argument(
                        'intMinArg',
                        Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['min' => -20]),
                    (new Argument(
                        'intMaxArg',
                        Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['max' => 20]),
                    (new Argument(
                        'intOneOfArg',
                        Container::Int(),
                    ))->addDirective(TestSchema::getType('intConstraint'), ['oneOf' => [1, 2, 3]]),
                    (new Argument(
                        'floatMinArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['min' => 4.01]),
                    (new Argument(
                        'floatMaxArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['max' => 20.101]),
                    (new Argument(
                        'floatOneOfArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::getType('floatConstraint'), ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new Argument(
                        'stringMinArg',
                        Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['minLength' => 4]),
                    (new Argument(
                        'stringMaxArg',
                        Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['maxLength' => 10]),
                    (new Argument(
                        'stringRegexArg',
                        Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['regex' => '/^(abc)|(foo)$/']),
                    (new Argument(
                        'stringOneOfArg',
                        Container::String(),
                    ))->addDirective(TestSchema::getType('stringConstraint'), ['oneOf' => ['abc', 'foo']]),
                    (new Argument(
                        'listMinArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 1]),
                    (new Argument(
                        'listMaxArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['maxItems' => 3]),
                    (new Argument(
                        'listUniqueArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), ['unique' => true]),
                    (new Argument(
                        'listInnerListArg',
                        Container::Int()->list()->list(),
                    ))->addDirective(TestSchema::getType('listConstraint'), [
                        'innerList' => (object) [
                            'minItems' => 1,
                            'maxItems' => 3,
                        ],
                    ]),
                    Argument::create('listMinIntMinArg', Container::Int()->list())
                        ->addDirective(TestSchema::getType('listConstraint'), ['minItems' => 3])
                        ->addDirective(TestSchema::getType('intConstraint'), ['min' => 3]),
                ]);
            }
        };
    }

    public static function getExactlyOneInput() : InputType
    {
        return new class extends InputType
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

            protected function getFieldDefinition() : ArgumentSet
            {
                return new ArgumentSet([
                    new Argument(
                        'int1',
                        Container::Int(),
                    ),
                    new Argument(
                        'int2',
                        Container::Int(),
                    ),
                ]);
            }
        };
    }

    public static function getAOrBType() : Type
    {
        return new class extends Type
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

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    new ResolvableField(
                        'fieldA',
                        Container::Int(),
                        static function (?int $parent) : ?int {
                            return $parent === 1
                                ? 1
                                : null;
                        },
                    ),
                    new ResolvableField(
                        'fieldB',
                        Container::Int(),
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
