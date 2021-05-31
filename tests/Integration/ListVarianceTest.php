<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class ListVarianceTest extends \PHPUnit\Framework\TestCase
{
    public function covarianceDataProvider() : array
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
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
            ],
            [
                ['maxItems' => 3],
                ['maxItems' => 4],
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
            ],
            [
                ['unique' => true],
                ['unique' => false],
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
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
        $interface = new class ($parent) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'listField',
                        \Graphpinator\Container\Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                \Graphpinator\Type\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Type\InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'listField',
                        \Graphpinator\Container\Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(\Graphpinator\Field\FieldSet::class, $type->getFields());
        }
    }

    public function covarianceDataProviderInner() : array
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
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
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
        $interface = new class ($parent) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'listField',
                        \Graphpinator\Container\Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                \Graphpinator\Type\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Type\InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : \Graphpinator\Value\TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'listField',
                        \Graphpinator\Container\Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(\Graphpinator\Field\FieldSet::class, $type->getFields());
        }
    }
}
