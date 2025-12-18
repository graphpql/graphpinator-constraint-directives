<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Container;
use Graphpinator\Typesystem\Exception\ArgumentDirectiveNotContravariant;
use Graphpinator\Typesystem\Field\Field;
use Graphpinator\Typesystem\Field\FieldSet;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\InterfaceSet;
use Graphpinator\Typesystem\InterfaceType;
use Graphpinator\Typesystem\Type;
use Graphpinator\Typesystem\Visitor\ValidateIntegrityVisitor;
use Graphpinator\Upload\UploadType;
use Graphpinator\Value\TypeIntermediateValue;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\UploadedFileInterface;

final class UploadVarianceTest extends TestCase
{
    public static function setUpBeforeClass() : void
    {
        parent::setUpBeforeClass();

        TestSchema::getSchema(); // init
    }

    public static function getUploadType() : Type
    {
        return new class extends Type
        {
            protected const NAME = 'UploadType';

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    new ResolvableField(
                        'fileContent',
                        Container::String(),
                        static function (?UploadedFileInterface $file) : string {
                            return $file->getStream()->getContents();
                        },
                    ),
                ]);
            }
        };
    }

    public static function covarianceDataProvider() : array
    {
        return [
            [
                ['maxSize' => 5_000],
                ['maxSize' => 5_000],
                null,
            ],
            [
                ['maxSize' => 5_000],
                [],
                null,
            ],
            [
                ['maxSize' => 5_000],
                ['maxSize' => 6_000],
                null,
            ],
            [
                ['mimeType' => ['text/plain', 'text/html']],
                ['mimeType' => ['text/plain', 'text/html']],
                null,
            ],
            [
                ['mimeType' => ['text/plain']],
                ['mimeType' => ['text/plain', 'text/html']],
                null,
            ],
            [
                ['maxSize' => 5_000],
                ['maxSize' => 4_000],
                ArgumentDirectiveNotContravariant::class,
            ],
            [
                ['mimeType' => ['text/plain', 'text/html']],
                ['mimeType' => ['text/plain']],
                ArgumentDirectiveNotContravariant::class,
            ],
            [
                [],
                ['maxSize' => 4_000],
                ArgumentDirectiveNotContravariant::class,
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
            protected const NAME = 'Interface';

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
                        'uploadField',
                        UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new ArgumentSet([
                        Argument::create(
                            'file',
                            new UploadType(),
                        )->addDirective(TestSchema::$uploadConstraint, $this->directiveArgs),
                    ])),
                ]);
            }
        };
        $type = new class ($interface, $child) extends InterfaceType {
            protected const NAME = 'Type';

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
                        'uploadField',
                        UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new ArgumentSet([
                        Argument::create(
                            'file',
                            new UploadType(),
                        )->addDirective(TestSchema::$uploadConstraint, $this->directiveArgs),
                    ])),
                ]);
            }
        };

        if (\is_string($exception)) {
            $this->expectException($exception);
            $type->accept(new ValidateIntegrityVisitor());
        }

        $this->expectNotToPerformAssertions();
    }
}
