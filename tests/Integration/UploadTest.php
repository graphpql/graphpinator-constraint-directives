<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

final class UploadTest extends \PHPUnit\Framework\TestCase
{
    public function simpleUploadDataProvider() : array
    {
        return [
            [
                ['maxSize' => 10000],
            ],
            [
                ['maxSize' => 5000],
            ],
            [
                ['mimeType' => ['text/plain']],
            ],
            [
                ['mimeType' => ['application/x-httpd-php', 'text/html', 'text/plain', 'application/pdf']],
            ],
        ];
    }

    public function invalidUploadDataProvider() : array
    {
        return [
            [
                ['maxSize' => 4999],
                \Graphpinator\ConstraintDirectives\Exception\MaxSizeConstraintNotSatisfied::class,
            ],
            [
                ['mimeType' => ['application/x-httpd-php']],
                \Graphpinator\ConstraintDirectives\Exception\MimeTypeConstraintNotSatisfied::class,
            ],
        ];
    }

    public function requestDataProvider() : array
    {
        return [
            [
                \Infinityloop\Utils\Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 10000],
            ],
            [
                \Infinityloop\Utils\Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 10000],
            ],
            [
                \Infinityloop\Utils\Json::fromNative((object) [
                    'query' => 'query queryName($var1: Upload = null) { fieldUpload(file: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{}',
                ['maxSize' => 10000],
            ],
        ];
    }

    public function requestInvalidDataProvider() : array
    {
        return [
            [
                \Infinityloop\Utils\Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 4999],
                \Graphpinator\ConstraintDirectives\Exception\MaxSizeConstraintNotSatisfied::class,
            ],
            [
                \Infinityloop\Utils\Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['mimeType' => ['application/x-httpd-php', 'application/pdf']],
                \Graphpinator\ConstraintDirectives\Exception\MimeTypeConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider simpleUploadDataProvider
     * @param array $constraint
     */
    public function testUploadSimple(array $constraint) : void
    {
        $request = \Infinityloop\Utils\Json::fromNative((object) [
            'query' => 'query queryName($var1: Upload) { fieldUpload(file: $var1) { fileContent } }',
            'variables' => (object) ['var1' => null],
        ]);

        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(\Psr\Http\Message\UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5000);
        $fileProvider = $this->createStub(\Graphpinator\Upload\FileProvider::class);
        $fileProvider->method('getMap')->willReturn(\Infinityloop\Utils\Json\MapJson::fromString('{ "0": ["variables.var1"] }'));
        $fileProvider->method('getFile')->willReturn($file);
        self::getGraphpinator($fileProvider, $constraint)
            ->run(new \Graphpinator\Request\JsonRequestFactory($request));

        self::assertTrue(true);
    }

    /**
     * @dataProvider invalidUploadDataProvider
     * @param string $exception
     * @param array $constraint
     */
    public function testUploadInvalid(array $constraint, string $exception) : void
    {
        $request = \Infinityloop\Utils\Json::fromNative((object) [
            'query' => 'query queryName($var1: Upload) { fieldUpload(file: $var1) { fileContent } }',
            'variables' => (object) ['var1' => null],
        ]);

        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(\Psr\Http\Message\UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5000);
        $fileProvider = $this->createStub(\Graphpinator\Upload\FileProvider::class);
        $fileProvider->method('getMap')->willReturn(\Infinityloop\Utils\Json\MapJson::fromString('{ "0": ["variables.var1"] }'));
        $fileProvider->method('getFile')->willReturn($file);

        self::expectException($exception);
        self::expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator($fileProvider, $constraint)
            ->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }

    /**
     * @dataProvider requestDataProvider
     * @param \Infinityloop\Utils\Json $request
     * @param string $map
     * @param array $constraint
     */
    public function testUploadRequest(\Infinityloop\Utils\Json $request, string $map, array $constraint) : void
    {
        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(\Psr\Http\Message\UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5000);
        $fileProvider = $this->createStub(\Graphpinator\Upload\FileProvider::class);
        $fileProvider->method('getMap')->willReturn(\Infinityloop\Utils\Json\MapJson::fromString($map));
        $fileProvider->method('getFile')->willReturn($file);
        self::getGraphpinator($fileProvider, $constraint)
            ->run(new \Graphpinator\Request\JsonRequestFactory($request));

        self::assertTrue(true);
    }

    /**
     * @dataProvider requestInvalidDataProvider
     * @param \Infinityloop\Utils\Json $request
     * @param string $map
     * @param array $constraint
     */
    public function testUploadRequestInvalid(\Infinityloop\Utils\Json $request, string $map, array $constraint, string $exception) : void
    {
        $stream = $this->createStub(\Psr\Http\Message\StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(\Psr\Http\Message\UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5000);
        $fileProvider = $this->createStub(\Graphpinator\Upload\FileProvider::class);
        $fileProvider->method('getMap')->willReturn(\Infinityloop\Utils\Json\MapJson::fromString($map));
        $fileProvider->method('getFile')->willReturn($file);

        self::expectException($exception);
        self::expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator($fileProvider, $constraint)
            ->run(new \Graphpinator\Request\JsonRequestFactory($request));
    }

    protected static function getGraphpinator(
        \Graphpinator\Upload\FileProvider $fileProvider,
        array $constraint,
    ) : \Graphpinator\Graphpinator
    {
        $query = new class ($constraint) extends \Graphpinator\Type\Type
        {
            protected const NAME = 'Query';

            public function __construct(protected array $constraint)
            {
                parent::__construct();
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : \Graphpinator\Field\ResolvableFieldSet
            {
                return new \Graphpinator\Field\ResolvableFieldSet([
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldUpload',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType(),
                        static function ($parent, ?\Psr\Http\Message\UploadedFileInterface $file) : ?\Psr\Http\Message\UploadedFileInterface {
                            return $file;
                        },
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        \Graphpinator\Argument\Argument::create(
                            'file',
                            new \Graphpinator\Upload\UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->constraint),
                    ])),
                    \Graphpinator\Field\ResolvableField::create(
                        'fieldMultiUpload',
                        \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType()->notNullList(),
                        static function ($parent, array $files) : array {
                            return $files;
                        },
                    )->setArguments(new \Graphpinator\Argument\ArgumentSet([
                        \Graphpinator\Argument\Argument::create(
                            'files',
                            (new \Graphpinator\Upload\UploadType())->list(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->constraint),
                    ])),
                ]);
            }
        };

        return new \Graphpinator\Graphpinator(
            new \Graphpinator\Type\Schema(
                new \Graphpinator\Container\SimpleContainer([
                    'Query' => $query,
                    'UploadType' => \Graphpinator\ConstraintDirectives\Tests\Integration\UploadVarianceTest::getUploadType(),
                    'Upload' => new \Graphpinator\Upload\UploadType(),
                ], []),
                $query,
            ),
            false,
            new \Graphpinator\Module\ModuleSet([
                new \Graphpinator\Upload\UploadModule($fileProvider),
            ]),
        );
    }
}
