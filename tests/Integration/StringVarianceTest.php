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

final class StringVarianceTest extends TestCase
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
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['maxLength' => 3],
                ['maxLength' => 4],
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['regex' => 'regexString'],
                ['regex' => 'differentString'],
                FieldDirectiveNotCovariant::class,
            ],
            [
                ['oneOf' => ['one', 'two']],
                ['oneOf' => ['zero', 'one', 'two', 'four']],
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
                        'stringField',
                        Container::String(),
                    )->addDirective(TestSchema::$stringConstraint, $this->directiveArgs),
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
                        'stringField',
                        Container::String(),
                    )->addDirective(TestSchema::$stringConstraint, $this->directiveArgs),
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
