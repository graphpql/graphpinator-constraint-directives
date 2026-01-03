<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied;
use Graphpinator\Graphpinator;
use Graphpinator\Request\JsonRequestFactory;
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;

final class ConstraintVariableTest extends TestCase
{
    public static function variableDataProvider() : array
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
     * @param Json $request
     */
    public function testVariable(Json $request) : void
    {
        self::assertSame(
            Json::fromNative((object) ['data' => ['field1' => 1]])->toString(),
            self::getGraphpinator()->run(new JsonRequestFactory($request))->toString(),
        );
    }

    public static function variableInvalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query ($str: String! @stringConstraint(minLength: 1, maxLength: 3)) { field1 }',
                    'variables' => (object) ['str' => 'aaaa'],
                ]),
                MaxLengthConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider variableInvalidDataProvider
     * @param Json $request
     * @param string $exception
     */
    public function testVariableInvalid(Json $request, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator()->run(new JsonRequestFactory($request));
    }

    protected static function getGraphpinator() : Graphpinator
    {
        $query = new class extends Type
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
                        'field1',
                        Container::Int(),
                        static function() : ?int {
                            return 1;
                        },
                    ),
                ]);
            }
        };

        return new Graphpinator(
            new Schema(
                new SimpleContainer([$query], [
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
