<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class IntVarianceTest extends \PHPUnit\Framework\TestCase
{
    public function covarianceDataProvider() : array
    {
        return [
            [
                [
                    'min' => 2,
                    'max' => 3,
                    'oneOf' => [1, 2]
                ],
                [
                    'min' => 2,
                    'max' => 3,
                    'oneOf' => [1, 1]
                ],
                null,
            ],
            [
                [],
                ['min' => 2],
                null,
            ],
            [
                ['max' => 4],
                ['max' => 3],
                null,
            ],
            [
                ['min' => 1],
                ['min' => 0],
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
            ],
            [
                ['max' => 3],
                ['max' => 4],
                \Graphpinator\Exception\Type\FieldDirectiveNotCovariant::class,
            ],
            [
                ['oneOf' => [1, 2]],
                ['oneOf' => [0, 1, 2, 3]],
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
        $interface = new class($parent) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                private array $directiveArgs,
            )
            {
                parent::__construct();
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'intField',
                        \Graphpinator\Container\Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), $this->directiveArgs),
                ]);
            }

            public function createResolvedValue(mixed $rawValue): \Graphpinator\Value\TypeIntermediateValue
            {
            }
        };
        $type = new class($interface, $child) extends \Graphpinator\Type\InterfaceType {
            public function __construct(
                \Graphpinator\Type\InterfaceType $interface,
                private array $directiveArgs,
            )
            {
                parent::__construct(new \Graphpinator\Utils\InterfaceSet([$interface]));
            }

            protected function getFieldDefinition() : \Graphpinator\Field\FieldSet
            {
                return new \Graphpinator\Field\FieldSet([
                    \Graphpinator\Field\Field::create(
                        'intField',
                        \Graphpinator\Container\Container::Int(),
                    )->addDirective(TestSchema::getType('intConstraint'), $this->directiveArgs),
                ]);
            }

            public function createResolvedValue(mixed $rawValue): \Graphpinator\Value\TypeIntermediateValue
            {
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
