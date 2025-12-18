<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\ConstraintDirectives\Exception\AtLeastConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\ExactlyConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MinLengthConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\RegexConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\UniqueConstraintNotSatisfied;
use Graphpinator\Graphpinator;
use Graphpinator\Request\JsonRequestFactory;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;

final class ConstraintArgumentTest extends TestCase
{
    public static function argumentDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {intMinArg: -20, intMaxArg: 20, intOneOfArg: 1}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {floatMinArg: 4.01, floatMaxArg: 20.101, floatOneOfArg: 1.01}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {stringMinArg: "abcd", stringMaxArg: "abcdefghij"}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {stringRegexArg: "foo", stringOneOfArg: "abc"}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {listMinArg: [1], listMaxArg: [1, 2, 3]}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {listUniqueArg: [1, 2, 3]}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {listInnerListArg: [[1, 2], [1, 3]]}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {listInnerListArg: [[1, 2], null]}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldInput(arg: {listMinIntMinArg: [3, 3, 3]}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldInput' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldExactlyOne(arg: {int1: 3}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldExactlyOne' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldExactlyOne(arg: {int2: 3}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldExactlyOne' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldExactlyOne(arg: {int1: null, int2: 3}) }',
                ]),
                Json::fromNative((object) ['data' => ['fieldExactlyOne' => 1]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldAOrB { fieldA fieldB } }',
                ]),
                Json::fromNative((object) ['data' => ['fieldAOrB' => ['fieldA' => null, 'fieldB' => 1]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldAOrB { fieldB } }',
                ]),
                Json::fromNative((object) ['data' => ['fieldAOrB' => ['fieldB' => 1]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName { fieldAOrB { fieldA } }',
                ]),
                Json::fromNative((object) ['data' => ['fieldAOrB' => ['fieldA' => null]]]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName {
                        fieldList(arg: [1,2,3])
                    }',
                ]),
                Json::fromNative((object) [
                    'data' => [
                        'fieldList' => [1,2,3],
                    ],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider argumentDataProvider
     * @param Json $request
     * @param Json $expected
     */
    public function testArgument(Json $request, Json $expected) : void
    {
        $graphpinator = new Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new JsonRequestFactory($request));

        self::assertSame($expected->toString(), $result->toString());
        self::assertNull($result->getErrors());
    }

    public static function argumentInvalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMinArg: -21}) }',
                ]),
                MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMaxArg: 21}) }',
                ]),
                MaxConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intOneOfArg: 4}) }',
                ]),
                OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatMinArg: 4.0}) }',
                ]),
                MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatMaxArg: 20.1011}) }',
                ]),
                MaxConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatOneOfArg: 2.03}) }',
                ]),
                OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringMinArg: "abc"}) }',
                ]),
                MinLengthConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringMaxArg: "abcdefghijk"}) }',
                ]),
                MaxLengthConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringOneOfArg: "abcd"}) }',
                ]),
                OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringRegexArg: "fooo"}) }',
                ]),
                RegexConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinArg: []}) }',
                ]),
                MinItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMaxArg: [1, 2, 3, 4]}) }',
                ]),
                MaxItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listUniqueArg: [1, 1]}) }',
                ]),
                UniqueConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinIntMinArg: [3]}) }',
                ]),
                MinItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinIntMinArg: [1, 1, 1]}) }',
                ]),
                MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {}) }',
                ]),
                AtLeastConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMinArg: null}) }',
                ]),
                AtLeastConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: 3, int2: 3}) }',
                ]),
                ExactlyConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {}) }',
                ]),
                ExactlyConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: null}) }',
                ]),
                ExactlyConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int2: null}) }',
                ]),
                ExactlyConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: null, int2: null}) }',
                ]),
                ExactlyConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query {
                        fieldList(arg: [1,2,3,4,5,6])
                    }',
                ]),
                MaxItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query {
                        fieldList(arg: [])
                    }',
                ]),
                MinItemsConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider argumentInvalidDataProvider
     * @param Json $request
     * @param string $exception
     */
    public function testArgumentInvalid(Json $request, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        $graphpinator = new Graphpinator(TestSchema::getSchema());
        $graphpinator->run(new JsonRequestFactory($request));
    }
}
