<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\Field\FieldSet;
use Graphpinator\Typesystem\InterfaceSet;
use Graphpinator\Typesystem\InterfaceType;
use Graphpinator\Value\TypeIntermediateValue;
use PHPUnit\Framework\TestCase;

final class ListVarianceTest extends TestCase
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
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['maxItems' => 3],
                ['maxItems' => 4],
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['unique' => true],
                ['unique' => false],
                FieldDirectiveNotCovariant::class,
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
        $interface = new class ($parent) extends InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : FieldSet
            {
                return new FieldSet([
                    Field::create(
                        'listField',
                        Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends InterfaceType {
            public function __construct(
                InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : FieldSet
            {
                return new FieldSet([
                    Field::create(
                        'listField',
                        Container::Int()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(FieldSet::class, $type->getFields());
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
                FieldDirectiveNotCovariant::class,
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
        $interface = new class ($parent) extends InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            public function createResolvedValue(mixed $rawValue) : TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : FieldSet
            {
                return new FieldSet([
                    Field::create(
                        'listField',
                        Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends InterfaceType {
            public function __construct(
                InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new InterfaceSet([$interface]));
            }

            public function createResolvedValue(mixed $rawValue) : TypeIntermediateValue
            {
            }

            protected function getFieldDefinition() : FieldSet
            {
                return new FieldSet([
                    Field::create(
                        'listField',
                        Container::Int()->list()->list(),
                    )->addDirective(TestSchema::getType('listConstraint'), $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->getFields();
        } else {
            self::assertInstanceOf(FieldSet::class, $type->getFields());
        }
    }
}
