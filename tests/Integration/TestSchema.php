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
    public static ?Type $query = null;
    public static ?InputType $constraintInput = null;
    public static ?InputType $exactlyOneInput = null;
    public static ?Type $constraintType = null;
    public static ?Type $aOrBType = null;
    public static ?ListConstraintInput $listConstraintInput = null;
    public static ?ObjectConstraintInput $objectConstraintInput = null;
    public static ?StringConstraintDirective $stringConstraint = null;
    public static ?IntConstraintDirective $intConstraint = null;
    public static ?FloatConstraintDirective $floatConstraint = null;
    public static ?ListConstraintDirective $listConstraint = null;
    public static ?ObjectConstraintDirective $objectConstraint = null;
    public static ?UploadConstraintDirective $uploadConstraint = null;
    private static ?ConstraintDirectiveAccessor $accessor = null;
    private static ?Container $container = null;

    public static function getSchema() : Schema
    {
        self::$accessor ??= self::createAccessor();
        self::$listConstraintInput ??= new ListConstraintInput(self::$accessor);
        self::$objectConstraintInput ??= new ObjectConstraintInput(self::$accessor);
        self::$stringConstraint ??= new StringConstraintDirective(self::$accessor);
        self::$intConstraint ??= new IntConstraintDirective(self::$accessor);
        self::$floatConstraint ??= new FloatConstraintDirective(self::$accessor);
        self::$listConstraint ??= new ListConstraintDirective(self::$accessor);
        self::$objectConstraint ??= new ObjectConstraintDirective(self::$accessor);
        self::$uploadConstraint ??= new UploadConstraintDirective(self::$accessor);
        self::$query ??= self::getQuery();
        self::$constraintInput ??= self::getConstraintInput();
        self::$exactlyOneInput ??= self::getExactlyOneInput();
        self::$constraintType ??= self::getConstraintType();
        self::$aOrBType ??= self::getAOrBType();
        self::$container ??= self::createContainer();

        return new Schema(
            self::$container,
            self::$query,
        );
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
                    TestSchema::$objectConstraint,
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
                        static function () : ?int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::$intConstraint, ['min' => -20]),
                    (new ResolvableField(
                        'intMaxField',
                        Container::Int(),
                        static function () : ?int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::$intConstraint, ['max' => 20]),
                    (new ResolvableField(
                        'intOneOfField',
                        Container::Int(),
                        static function () : ?int {
                            return 1;
                        },
                    ))->addDirective(TestSchema::$intConstraint, ['oneOf' => [1, 2, 3]]),
                    (new ResolvableField(
                        'floatMinField',
                        Container::Float(),
                        static function () : ?float {
                            return 4.02;
                        },
                    ))->addDirective(TestSchema::$floatConstraint, ['min' => 4.01]),
                    (new ResolvableField(
                        'floatMaxField',
                        Container::Float(),
                        static function () : ?float {
                            return 1.1;
                        },
                    ))->addDirective(TestSchema::$floatConstraint, ['max' => 20.101]),
                    (new ResolvableField(
                        'floatOneOfField',
                        Container::Float(),
                        static function () {
                            return 1.01;
                        },
                    ))->addDirective(TestSchema::$floatConstraint, ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new ResolvableField(
                        'stringMinField',
                        Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::$stringConstraint, ['minLength' => 4]),
                    (new ResolvableField(
                        'stringMaxField',
                        Container::String(),
                        static function () {
                            return 1;
                        },
                    ))->addDirective(TestSchema::$stringConstraint, ['maxLength' => 10]),
                    (new ResolvableField(
                        'listMinField',
                        Container::Int()->list(),
                        static function () : ?array {
                            return [1];
                        },
                    ))->addDirective(TestSchema::$listConstraint, ['minItems' => 1]),
                    (new ResolvableField(
                        'listMaxField',
                        Container::Int()->list(),
                        static function () : ?array {
                            return [1, 2];
                        },
                    ))->addDirective(TestSchema::$listConstraint, ['maxItems' => 3]),
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
                    TestSchema::$objectConstraint,
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
                    ))->addDirective(TestSchema::$intConstraint, ['min' => -20]),
                    (new Argument(
                        'intMaxArg',
                        Container::Int(),
                    ))->addDirective(TestSchema::$intConstraint, ['max' => 20]),
                    (new Argument(
                        'intOneOfArg',
                        Container::Int(),
                    ))->addDirective(TestSchema::$intConstraint, ['oneOf' => [1, 2, 3]]),
                    (new Argument(
                        'floatMinArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::$floatConstraint, ['min' => 4.01]),
                    (new Argument(
                        'floatMaxArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::$floatConstraint, ['max' => 20.101]),
                    (new Argument(
                        'floatOneOfArg',
                        Container::Float(),
                    ))->addDirective(TestSchema::$floatConstraint, ['oneOf' => [1.01, 2.02, 3.0]]),
                    (new Argument(
                        'stringMinArg',
                        Container::String(),
                    ))->addDirective(TestSchema::$stringConstraint, ['minLength' => 4]),
                    (new Argument(
                        'stringMaxArg',
                        Container::String(),
                    ))->addDirective(TestSchema::$stringConstraint, ['maxLength' => 10]),
                    (new Argument(
                        'stringRegexArg',
                        Container::String(),
                    ))->addDirective(TestSchema::$stringConstraint, ['regex' => '/^(abc)|(foo)$/']),
                    (new Argument(
                        'stringOneOfArg',
                        Container::String(),
                    ))->addDirective(TestSchema::$stringConstraint, ['oneOf' => ['abc', 'foo']]),
                    (new Argument(
                        'listMinArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::$listConstraint, ['minItems' => 1]),
                    (new Argument(
                        'listMaxArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::$listConstraint, ['maxItems' => 3]),
                    (new Argument(
                        'listUniqueArg',
                        Container::Int()->list(),
                    ))->addDirective(TestSchema::$listConstraint, ['unique' => true]),
                    (new Argument(
                        'listInnerListArg',
                        Container::Int()->list()->list(),
                    ))->addDirective(TestSchema::$listConstraint, [
                        'innerList' => (object) [
                            'minItems' => 1,
                            'maxItems' => 3,
                        ],
                    ]),
                    Argument::create('listMinIntMinArg', Container::Int()->list())
                        ->addDirective(TestSchema::$listConstraint, ['minItems' => 3])
                        ->addDirective(TestSchema::$intConstraint, ['min' => 3]),
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
                    TestSchema::$objectConstraint,
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
                    TestSchema::$objectConstraint,
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

    private static function createAccessor() : ConstraintDirectiveAccessor
    {
        return new class implements ConstraintDirectiveAccessor
        {
            public function getString() : StringConstraintDirective
            {
                return TestSchema::$stringConstraint;
            }

            public function getInt() : IntConstraintDirective
            {
                return TestSchema::$intConstraint;
            }

            public function getFloat() : FloatConstraintDirective
            {
                return TestSchema::$floatConstraint;
            }

            public function getList() : ListConstraintDirective
            {
                return TestSchema::$listConstraint;
            }

            public function getListInput() : ListConstraintInput
            {
                return TestSchema::$listConstraintInput;
            }

            public function getObject() : ObjectConstraintDirective
            {
                return TestSchema::$objectConstraint;
            }

            public function getObjectInput() : ObjectConstraintInput
            {
                return TestSchema::$objectConstraintInput;
            }

            public function getUpload() : UploadConstraintDirective
            {
                return TestSchema::$uploadConstraint;
            }
        };
    }

    private static function createContainer() : Container
    {
        return new SimpleContainer([
            'Query' => self::$query,
            'ConstraintInput' => self::$constraintInput,
            'ExactlyOneInput' => self::$exactlyOneInput,
            'ConstraintType' => self::$constraintType,
            'ListConstraintInput' => self::$listConstraintInput,
        ], [
            'stringConstraint' => self::$stringConstraint,
            'intConstraint' => self::$intConstraint,
            'floatConstraint' => self::$floatConstraint,
            'listConstraint' => self::$listConstraint,
            'objectConstraint' => self::$objectConstraint,
            'uploadConstraint' => self::$uploadConstraint,
        ]);
    }

    private static function getQuery() : Type
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
                        static function ($parent, \stdClass $arg) : ?int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'arg',
                            TestSchema::$constraintInput,
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldExactlyOne',
                        Container::Int(),
                        static function ($parent, \stdClass $arg) : ?int {
                            return 1;
                        },
                    )->setArguments(new ArgumentSet([
                        new Argument(
                            'arg',
                            TestSchema::$exactlyOneInput,
                        ),
                    ])),
                    ResolvableField::create(
                        'fieldAOrB',
                        TestSchema::$aOrBType->notNull(),
                        static function ($parent) : int {
                            return 0;
                        },
                    ),
                    ResolvableField::create(
                        'fieldList',
                        Container::Int()->list(),
                        static function ($parent, array $arg) : ?array {
                            return $arg;
                        },
                    )->addDirective(
                        TestSchema::$listConstraint,
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
}
