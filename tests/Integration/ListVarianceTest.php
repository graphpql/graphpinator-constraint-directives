<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class ListVarianceTest extends \PHPUnit\Framework\TestCase
{
    public static function covarianceDataProvider() : array
    {
        return [
            [
                [
                    'minItems' => 2,
                    'maxItems' => 3,
                    'unique' => true,
                ],
                [
                    'minItems' => 2,
                    'maxItems' => 3,
                    'unique' => true,
                ],
                null,
            ],
            [
                [],
                ['minItems' => 2],
                null,
            ],
            [
                ['maxItems' => 4],
                ['maxItems' => 3],
                null,
            ],
            [
                ['minItems' => 1],
                ['minItems' => 0],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['maxItems' => 3],
                ['maxItems' => 4],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['unique' => true],
                ['unique' => false],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
        ];
    }

    /**
     * @dataProvider covarianceDataProvider
     * @param array $parent
     * @param array $child
     * @param string|null $exception
     */
    public function testCovariance(array $parent, array $child, ?string $exception) : void
    {
        $interface = new class ($parent) extends \Graphpinator\Typesystem\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    \Graphpinator\Typesystem\Field\Field::create(
                        'listField',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends \Graphpinator\Typesystem\InterfaceType {
            public function __construct(
                \Graphpinator\Typesystem\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Typesystem\InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    \Graphpinator\Typesystem\Field\Field::create(
                        'listField',
                        \Graphpinator\Typesystem\Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(\Graphpinator\Typesystem\Field\FieldSet::class, $type->getFields());
        }
    }

    public static function covarianceDataProviderInner() : array
    {
        return [
            [
                [
                    'innerList' => (object) ['minItems' => 2],
                ],
                [
                    'innerList' => (object) ['minItems' => 2],
                ],
                null,
            ],
            [
                [],
                [
                    'innerList' => (object) ['minItems' => 2],
                ],
                null,
            ],
            [
                [
                    'innerList' => (object) ['minItems' => 2],
                ],
                [
                    'innerList' => (object) ['minItems' => 3],
                ],
                null,
            ],
            [
                [
                    'innerList' => (object) ['minItems' => 2],
                ],
                [],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
        ];
    }

    /**
     * @dataProvider covarianceDataProviderInner
     * @param array $parent
     * @param array $child
     * @param string|null $exception
     */
    public function testCovarianceInner(array $parent, array $child, ?string $exception) : void
    {
        $interface = new class ($parent) extends \Graphpinator\Typesystem\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    \Graphpinator\Typesystem\Field\Field::create(
                        'listField',
                        \Graphpinator\Typesystem\Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends \Graphpinator\Typesystem\InterfaceType {
            public function __construct(
                \Graphpinator\Typesystem\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Typesystem\InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\FieldSet
            {
                return new \Graphpinator\Typesystem\Field\FieldSet([
                    \Graphpinator\Typesystem\Field\Field::create(
                        'listField',
                        \Graphpinator\Typesystem\Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(\Graphpinator\Typesystem\Field\FieldSet::class, $type->getFields());
        }
    }
}
