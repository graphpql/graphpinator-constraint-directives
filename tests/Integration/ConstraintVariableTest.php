<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use \Infinityloop\Utils\Json;

final class ConstraintVariableTest extends \PHPUnit\Framework\TestCase
{
    public function variableDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query ($var: Int! @intConstraint(min: 0, max: 1, oneOf: [1])) { field1 }',
                    'variables' => (object) ['var' => 1],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query ($var: [Int!]! @intConstraint(min: 0, max: 1, oneOf: [0,1])) { field1 }',
                    'variables' => (object) ['var' => [1, 0, 0, 0, 1]],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query ($var: String! @stringConstraint(minLength: 1, maxLength: 3)) { field1 }',
                    'variables' => (object) ['var' => 'aaa'],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query ($var: Float! @floatConstraint(min: 1.0, max: 3.0)) { field1 }',
                    'variables' => (object) ['var' => 2.9],
                ]),
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query ($var: [Int!]! @listConstraint(minItems: 1, maxItems: 3)) { field1 }',
                    'variables' => (object) ['var' => [1, 0, 1]],
                ]),
            ],
        ];
    }

    /**
     * @dataProvider variableDataProvider
     * @param \Infinityloop\Utils\Json $request
     */
    public function testVariable(Json $request) : void
    {
        self::assertSame(
            Json::fromNative((object) ['data' => ['field1' => 1]])->toString(),
            self::getGraphpinator()->run(new \Graphpinator\Request\JsonRequestFactory($request))->toString(),
        );
    }

    public function variableInvalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query ($str: String! @stringConstraint(minLength: 1, maxLength: 3)) { field1 }',
                    'variables' => (object) ['str' => 'aaaa'],
                ]),
                \Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider variableInvalidDataProvider
     * @param \Infinityloop\Utils\Json $request
     * @param string $exception
     */
    public function testVariableInvalid(Json $request, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator()->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }

    protected static function getGraphpinator() : \Graphpinator\Graphpinator
    {
        $query = new class extends \Graphpinator\Type\Type
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
                        'field1',
                        \Graphpinator\Typesystem\Container::Int(),
                        static function() : int {
                            return 1;
                        },
                    ),
                ]);
            }
        };

        return new \Graphpinator\Graphpinator(
            new \Graphpinator\Type\Schema(
                new \Graphpinator\Container\SimpleContainer([$query], [
                    ConstructTest::getInt(),
                    ConstructTest::getFloat(),
                    ConstructTest::getString(),
                    ConstructTest::getList(),
                ]),
                $query,
            ),
        );
    }
}
