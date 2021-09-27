<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class UploadVarianceTest extends \PHPUnit\Framework\TestCase
{
    public static function getUploadType() : \Graphpinator\Typesystem\Type
    {
        return new class extends \Graphpinator\Typesystem\Type
        {
            protected const NAME = 'UploadType';

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Typesystem\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Typesystem\Field\ResolvableFieldSet([
                    new \Graphpinator\Typesystem\Field\ResolvableField(
                        'fileContent',
                        \Graphpinator\Typesystem\Container::String(),
                        static function (?\Psr\Http\Message\UploadedFileInterface $file) : string {
                            return $file->getStream()->getContents();
                        },
                    ),
                ]);
            }
        };
    }

    public function covarianceDataProvider() : array
    {
        return [
            [
                ['maxSize' => 5000],
                ['maxSize' => 5000],
                null,
            ],
            [
                ['maxSize' => 5000],
                [],
                null,
            ],
            [
                ['maxSize' => 5000],
                ['maxSize' => 6000],
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
                ['maxSize' => 5000],
                ['maxSize' => 4000],
                \Graphpinator\Typesystem\Exception\ArgumentDirectiveNotContravariant::class,
            ],
            [
                ['mimeType' => ['text/plain', 'text/html']],
                ['mimeType' => ['text/plain']],
                \Graphpinator\Typesystem\Exception\ArgumentDirectiveNotContravariant::class,
            ],
            [
                [],
                ['maxSize' => 4000],
                \Graphpinator\Typesystem\Exception\ArgumentDirectiveNotContravariant::class,
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
            protected const NAME = 'Interface';

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
                        'uploadField',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create(
                            'file',
                            new \Graphpinator\Upload\UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->directiveArgs),
                    ])),
                ]);
            }
        };
        $type = new class ($interface, $child) extends \Graphpinator\Typesystem\InterfaceType {
            protected const NAME = 'Type';

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
                        'uploadField',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType()->notNull(),
                    )->setArguments(new \Graphpinator\Typesystem\Argument\ArgumentSet([
                        \Graphpinator\Typesystem\Argument\Argument::create(
                            'file',
                            new \Graphpinator\Upload\UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->directiveArgs),
                    ])),
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
