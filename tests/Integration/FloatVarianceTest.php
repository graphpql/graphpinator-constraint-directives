<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class FloatVarianceTest extends \PHPUnit\Framework\TestCase
{
    public function covarianceDataProvider() : array
    {
        return [
            [
                [
                    'min' => 2.0,
                    'max' => 3.0,
                    'oneOf' => [1.1, 1.2],
                ],
                [
                    'min' => 2.0,
                    'max' => 3.0,
                    'oneOf' => [1.1, 1.2],
                ],
                null,
            ],
            [
                [],
                ['min' => 2.0],
                null,
            ],
            [
                ['max' => 4.0],
                ['max' => 3.0],
                null,
            ],
            [
                ['min' => 1.0],
                ['min' => 0.0],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['max' => 3.0],
                ['max' => 4.0],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['oneOf' => [1.1, 1.2]],
                ['oneOf' => [1.0, 1.1, 1.2, 1.3]],
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
                        'floatField',
                        \Graphpinator\Typesystem\Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), $this->directiveArgs),
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
                        'floatField',
                        \Graphpinator\Typesystem\Container::Float(),
                    )->addDirective(TestSchema::getType('floatConstraint'), $this->directiveArgs),
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
