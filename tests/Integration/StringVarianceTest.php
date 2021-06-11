<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class StringVarianceTest extends \PHPUnit\Framework\TestCase
{
    public function covarianceDataProvider() : array
    {
        return [
            [
                [
                    'minLength' => 2,
                    'maxLength' => 3,
                    'regex' => 'regexString',
                    'oneOf' => ['one', 'two'],
                ],
                [
                    'minLength' => 2,
                    'maxLength' => 3,
                    'regex' => 'regexString',
                    'oneOf' => ['one', 'two'],
                ],
                null,
            ],
            [
                [],
                ['minLength' => 2],
                null,
            ],
            [
                ['maxLength' => 4],
                ['maxLength' => 3],
                null,
            ],
            [
                ['minLength' => 1],
                ['minLength' => 0],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['maxLength' => 3],
                ['maxLength' => 4],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['regex' => 'regexString'],
                ['regex' => 'differentString'],
                \Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant::class,
            ],
            [
                ['oneOf' => ['one', 'two']],
                ['oneOf' => ['zero', 'one', 'two', 'four']],
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
                        'stringField',
                        \Graphpinator\Typesystem\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), $this->directiveArgs),
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
                        'stringField',
                        \Graphpinator\Typesystem\Container::String(),
                    )->addDirective(TestSchema::getType('stringConstraint'), $this->directiveArgs),
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
