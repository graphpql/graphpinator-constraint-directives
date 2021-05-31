<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use \Infinityloop\Utils\Json;

final class ConstraintArgumentTest extends \PHPUnit\Framework\TestCase
{
    public function argumentDataProvider() : array
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
     * @param \Infinityloop\Utils\Json $request
     * @param \Infinityloop\Utils\Json $expected
     */
    public function testArgument(Json $request, Json $expected) : void
    {
        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $result = $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));

        self::assertSame($expected->toString(), $result->toString());
        self::assertNull($result->getErrors());
    }

    public function argumentInvalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMinArg: -21}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMaxArg: 21}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intOneOfArg: 4}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatMinArg: 4.0}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatMaxArg: 20.1011}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {floatOneOfArg: 2.03}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringMinArg: "abc"}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinLengthConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringMaxArg: "abcdefghijk"}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringOneOfArg: "abcd"}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {stringRegexArg: "fooo"}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\RegexConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinArg: []}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMaxArg: [1, 2, 3, 4]}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listUniqueArg: [1, 1]}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\UniqueConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinIntMinArg: [3]}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {listMinIntMinArg: [1, 1, 1]}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\AtLeastOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldInput(arg: {intMinArg: null}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\AtLeastOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: 3, int2: 3}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\ExactlyOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\ExactlyOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: null}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\ExactlyOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int2: null}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\ExactlyOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query { fieldExactlyOne(arg: {int1: null, int2: null}) }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\ExactlyOneConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query {
                        fieldList(arg: [1,2,3,4,5,6])
                    }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query {
                        fieldList(arg: [])
                    }',
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider argumentInvalidDataProvider
     * @param \Infinityloop\Utils\Json $request
     * @param string $exception
     */
    public function testArgumentInvalid(Json $request, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        $graphpinator = new \Graphpinator\Graphpinator(TestSchema::getSchema());
        $graphpinator->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }
}
