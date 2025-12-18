<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Exception\FieldDirectiveNotCovariant;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\Field\FieldSet;
use Graphpinator\Typesystem\InterfaceSet;
use Graphpinator\Typesystem\InterfaceType;
use Graphpinator\Typesystem\Visitor\ValidateIntegrityVisitor;
use Graphpinator\Value\TypeIntermediateValue;
use PHPUnit\Framework\TestCase;

final class FloatVarianceTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        TestSchema::getSchema(); // init
    }

    public static function covarianceDataProvider() : array
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
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['max' => 3.0],
                ['max' => 4.0],
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['oneOf' => [1.1, 1.2]],
                ['oneOf' => [1.0, 1.1, 1.2, 1.3]],
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
                private readonly array $directiveArgs,
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
                        'floatField',
                        Container::Float(),
                    )->addDirective(TestSchema::$floatConstraint, $this->directiveArgs),
                ]);
            }
        };
        $type = new class ($interface, $child) extends InterfaceType {
            public function __construct(
                InterfaceType $interface,
                private readonly array $directiveArgs,
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
                        'floatField',
                        Container::Float(),
                    )->addDirective(TestSchema::$floatConstraint, $this->directiveArgs),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->accept(new ValidateIntegrityVisitor());

            return;
        }

        $this->expectNotToPerformAssertions();
    }
}
