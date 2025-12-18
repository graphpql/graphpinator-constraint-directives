<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

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
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;

final class ConstraintFieldTest extends TestCase
{
    public static function fieldDataProvider() : array
    {
        return [
            [
                [
                    'type' => Container::Int(),
                    'value' => -19,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => -20],
                ],
                Json::fromNative((object) ['data' => ['field1' => -19]]),
            ],
            [
                [
                    'type' => Container::Int(),
                    'value' => 19,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => 20],
                ],
                Json::fromNative((object) ['data' => ['field1' => 19]]),
            ],
            [
                [
                    'type' => Container::Int(),
                    'value' => 2,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['oneOf' => [1, 2, 3]],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2]]),
            ],
            [
                [
                    'type' => Container::Int()->notNullList(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => 1],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => 2],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['unique' => true],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                Json::fromNative((object) ['data' => ['field1' => [1, 2]]]),
            ],
            [
                [
                    'type' => Container::Int()->list()->list(),
                    'value' => [[1, 2]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['innerList' => (object) ['minItems' => 2, 'maxItems' => 3, 'unique' => true]],
                ],
                Json::fromNative((object) ['data' => ['field1' => [[1, 2]]]]),
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 1.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['min' => 0.99],
                ],
                Json::fromNative((object) ['data' => ['field1' => 1.00]]),
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 2.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['max' => 2.01],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2.00]]),
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 2.00,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['oneOf' => [1.05, 2.00, 2.05]],
                ],
                Json::fromNative((object) ['data' => ['field1' => 2.00]]),
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'Shrek',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['minLength' => 4],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'Shrek']]),
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'abc',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 4],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'abc']]),
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'beetlejuice',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['regex' => '/^(shrek)|(beetlejuice)$/'],
                ],
                Json::fromNative((object) ['data' => ['field1' => 'beetlejuice']]),
            ],
            [
                [
                    'type' => Container::String()->notNullList(),
                    'value' => ['valid', 'valid'],
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 5],
                ],
                Json::fromNative((object) ['data' => ['field1' => ['valid', 'valid']]]),
            ],
            [
                [
                    'type' => Container::Float()->notNullList(),
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
     * @param Json $expected
     */
    public function testField(array $settings, Json $expected) : void
    {
        $request = Json::fromNative((object) [
            'query' => 'query { field1 }',
        ]);

        self::assertSame(
            $expected->toString(),
            self::getGraphpinator($settings)->run(new JsonRequestFactory($request))->toString(),
        );
    }

    public static function fieldInvalidDataProvider() : array
    {
        return [
            [
                [
                    'type' => Container::Int(),
                    'value' => -25,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['min' => -20],
                ],
                MinConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int(),
                    'value' => 25,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['max' => -20],
                ],
                MaxConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int(),
                    'value' => 5,
                    'directive' => TestSchema::getType('intConstraint'),
                    'constraint' => ['oneOf' => [1, 2, 3]],
                ],
                OneOfConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 3],
                ],
                MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2, 3],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['maxItems' => 2],
                ],
                MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2, 2, 3],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['unique' => true],
                ],
                UniqueConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2, 3, 4],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list(),
                    'value' => [1, 2, 2],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => ['minItems' => 2, 'maxItems' => 3, 'unique' => true],
                ],
                UniqueConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list()->list(),
                    'value' => [[1]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => [
                        'innerList' => (object) [
                            'minItems' => 2,
                            'maxItems' => 3,
                        ],
                    ],
                ],
                MinItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Int()->list()->list(),
                    'value' => [[1, 2, 3, 4]],
                    'directive' => TestSchema::getType('listConstraint'),
                    'constraint' => [
                        'innerList' => (object) [
                            'minItems' => 2,
                            'maxItems' => 3,
                        ],
                    ],
                ],
                MaxItemsConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 0.10,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['min' => 0.99],
                ],
                MinConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 2.01,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['max' => 2.00],
                ],
                MaxConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::Float(),
                    'value' => 5.35,
                    'directive' => TestSchema::getType('floatConstraint'),
                    'constraint' => ['oneOf' => [1.05, 2.00, 2.05]],
                ],
                OneOfConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'abc',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['minLength' => 4],
                ],
                MinLengthConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'Shrek',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 4],
                ],
                MaxLengthConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::String(),
                    'value' => 'invalid',
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['regex' => '/^(shrek)|(beetlejuice)$/'],
                ],
                RegexConstraintNotSatisfied::class,
            ],
            [
                [
                    'type' => Container::String()->notNullList(),
                    'value' => ['valid', 'invalid'],
                    'directive' => TestSchema::getType('stringConstraint'),
                    'constraint' => ['maxLength' => 5],
                ],
                MaxLengthConstraintNotSatisfied::class,
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

        self::getGraphpinator($settings)->run(new JsonRequestFactory($request));
    }

    protected static function getGraphpinator(array $settings) : Graphpinator
    {
        $query = new class ($settings) extends Type
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

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'field1',
                        $this->settings['type'],
                        function() : mixed {
                            return $this->settings['value'];
                        },
                    )->addDirective($this->settings['directive'], $this->settings['constraint']),
                ]);
            }
        };

        return new Graphpinator(
            new Schema(
                new SimpleContainer([$query], []),
                $query,
            ),
        );
    }
}
