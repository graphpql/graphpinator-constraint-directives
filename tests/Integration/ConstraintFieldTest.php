<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use \Infinityloop\Utils\Json;

final class ConstraintFieldTest extends \PHPUnit\Framework\TestCase
{
    public function fieldDataProvider() : array
    {
        return [
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => -19,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => -20],
                ],
                Json::fromNative((object) ['data' => ['field1' => -19]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => 19,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => 20],
                ],
                Json::fromNative((object) ['data' => ['field1' => 19]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => 2,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['oneOf' => [1, 2, 3]],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->notNullList(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => 1],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => 2],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['unique' => true],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list()->list(),
                    'value' => [[1, 2]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['innerList' => (object) ['minItems' => 2, 'maxItems' => 3, 'unique' => true]],
                ],
                Json::fromNative((object) ['data' => ['field1' => [[1, 2]]]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 1.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['min' => 0.99],
                ],
                Json::fromNative((object) ['data' => ['field1' => 1.00]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 2.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['max' => 2.01],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2.00]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 2.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['oneOf' => [1.05, 2.00, 2.05]],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2.00]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'Shrek',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['minLength' => 4],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'Shrek']]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'abc',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 4],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'abc']]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'beetlejuice',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['regex' => '/^(shrek)|(beetlejuice)$/'],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'beetlejuice']]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String()->notNullList(),
                    'value' => ['valid', 'valid'],
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 5],
                ],
                Json::fromNative((object) ['data' => ['field1' => ['valid', 'valid']]]),
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float()->notNullList(),
                    'value' => [1.00, 2.00, 3.00],
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['min' => 1.00, 'max' => 3.00],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1.00, 2.00, 3.00]]]),
            ],
        ];
    }

    /**
     * @dataProvider fieldDataProvider
     * @param array $settings
     * @param \Infinityloop\Utils\Json $expected
     */
    public function testField(array $settings, Json $expected) : void
    {
        $request = Json::fromNative((object) [
            'query' => 'query { field1 }',
        ]);

        self::assertSame(
            $expected->toString(),
            self::getGraphpinator($settings)->run(new \Graphpinator\Request\JsonRequestFactory($request))->toString(),
        );
    }

    public function fieldInvalidDataProvider() : array
    {
        return [
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => -25,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => -20],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => 25,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => -20],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int(),
                    'value' => 5,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['oneOf' => [1, 2, 3]],
                ],
                \Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 3],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2, 3],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['maxItems' => 2],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2, 2, 3],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['unique' => true],
                ],
                \Graphpinator\ConstraintDirectives\Exception\UniqueConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2, 3, 4],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list(),
                    'value' => [1, 2, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                \Graphpinator\ConstraintDirectives\Exception\UniqueConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list()->list(),
                    'value' => [[1]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => [
                        'innerList' => (object) [
                            'minItems' => 2,
                            'maxItems' => 3,
                        ],
                    ],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Int()->list()->list(),
                    'value' => [[1, 2, 3, 4]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => [
                        'innerList' => (object) [
                            'minItems' => 2,
                            'maxItems' => 3,
                        ],
                    ],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 0.10,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['min' => 0.99],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 2.01,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['max' => 2.00],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::Float(),
                    'value' => 5.35,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['oneOf' => [1.05, 2.00, 2.05]],
                ],
                \Graphpinator\ConstraintDirectives\Exception\OneOfConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'abc',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['minLength' => 4],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MinLengthConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'Shrek',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 4],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String(),
                    'value' => 'invalid',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['regex' => '/^(shrek)|(beetlejuice)$/'],
                ],
                \Graphpinator\ConstraintDirectives\Exception\RegexConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => \Graphpinator\Container\Container::String()->notNullList(),
                    'value' => ['valid', 'invalid'],
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 5],
                ],
                \Graphpinator\ConstraintDirectives\Exception\MaxLengthConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider fieldInvalidDataProvider
     * @param array $settings
     * @param string $exception
     */
    public function testFieldInvalid(array $settings, string $exception) : void
    {
        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        $request = Json::fromNative((object) [
            'query' => 'query { field1 }',
        ]);

        self::getGraphpinator($settings)->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }

    protected static function getGraphpinator(array $settings) : \Graphpinator\Graphpinator
    {
        $query = new class ($settings) extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            public function __construct(
                private array $settings,
            )
            {
                parent::__construct();
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    \Graphpinator\Field\ResolvableField::create(
                        'field1',
                        $this->settings['type'],
                        function() : mixed {
                            return $this->settings['value'];
                        },
                    )->addDirective($this->settings['directive'], $this->settings['constraint']),
                ]);
            }
        };

        return new \Graphpinator\Graphpinator(
            new \Graphpinator\Type\Schema(
                new \Graphpinator\Container\SimpleContainer([$query], []),
                $query,
            ),
        );
    }
}
