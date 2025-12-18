<?php

declare(strict_types = 1);

namespace Graphpinator\ConstraintDirectives\Tests\Integration;

use Graphpinator\ConstraintDirectives\Exception\MaxSizeConstraintNotSatisfied;
use Graphpinator\ConstraintDirectives\Exception\MimeTypeConstraintNotSatisfied;
use Graphpinator\Graphpinator;
use Graphpinator\Module\ModuleSet;
use Graphpinator\Request\JsonRequestFactory;
use Graphpinator\SimpleContainer;
use Graphpinator\Typesystem\Argument\Argument;
use Graphpinator\Typesystem\Argument\ArgumentSet;
use Graphpinator\Typesystem\Field\ResolvableField;
use Graphpinator\Typesystem\Field\ResolvableFieldSet;
use Graphpinator\Typesystem\Schema;
use Graphpinator\Typesystem\Type;
use Graphpinator\Upload\FileProvider;
use Graphpinator\Upload\UploadModule;
use Graphpinator\Upload\UploadType;
use Infinityloop\Utils\Json;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\UploadedFileInterface;

final class UploadTest extends TestCase
{
    public static function simpleUploadDataProvider() : array
    {
        return [
            [
                ['maxSize' => 10_000],
            ],
            [
                ['maxSize' => 5_000],
            ],
            [
                ['mimeType' => ['text/plain']],
            ],
            [
                ['mimeType' => ['application/x-httpd-php', 'text/html', 'text/plain', 'application/pdf']],
            ],
        ];
    }

    public static function invalidUploadDataProvider() : array
    {
        return [
            [
                ['maxSize' => 4_999],
                MaxSizeConstraintNotSatisfied::class,
            ],
            [
                ['mimeType' => ['application/x-httpd-php']],
                MimeTypeConstraintNotSatisfied::class,
            ],
        ];
    }

    public static function requestDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 10_000],
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 10_000],
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName($var1: Upload = null) { fieldUpload(file: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{}',
                ['maxSize' => 10_000],
            ],
        ];
    }

    public static function requestInvalidDataProvider() : array
    {
        return [
            [
                Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['maxSize' => 4_999],
                MaxSizeConstraintNotSatisfied::class,
            ],
            [
                Json::fromNative((object) [
                    'query' => 'query queryName($var1: [Upload]) { fieldMultiUpload(files: $var1) { fileContent } }',
                    'variables' => (object) ['var1' => null],
                ]),
                '{ "0": ["variables.var1.0", "variables.var1.1"] }',
                ['mimeType' => ['application/x-httpd-php', 'application/pdf']],
                MimeTypeConstraintNotSatisfied::class,
            ],
        ];
    }

    /**
     * @dataProvider simpleUploadDataProvider
     * @param array $constraint
     */
    public function testUploadSimple(array $constraint) : void
    {
        $request = Json::fromNative((object) [
            'query' => 'query queryName($var1: Upload) { fieldUpload(file: $var1) { fileContent } }',
            'variables' => (object) ['var1' => null],
        ]);

        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5_000);
        $fileProvider = $this->createStub(FileProvider::class);
        $fileProvider->method('getMap')->willReturn(Json::fromString('{ "0": ["variables.var1"] }'));
        $fileProvider->method('getFile')->willReturn($file);
        self::getGraphpinator($fileProvider, $constraint)
            ->run(new JsonRequestFactory($request));

        self::assertTrue(true);
    }

    /**
     * @dataProvider invalidUploadDataProvider
     * @param array $constraint
     * @param string $exception
     */
    public function testUploadInvalid(array $constraint, string $exception) : void
    {
        $request = Json::fromNative((object) [
            'query' => 'query queryName($var1: Upload) { fieldUpload(file: $var1) { fileContent } }',
            'variables' => (object) ['var1' => null],
        ]);

        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5_000);
        $fileProvider = $this->createStub(FileProvider::class);
        $fileProvider->method('getMap')->willReturn(Json::fromString('{ "0": ["variables.var1"] }'));
        $fileProvider->method('getFile')->willReturn($file);

        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator($fileProvider, $constraint)
            ->run(new JsonRequestFactory($request));
    }

    /**
     * @dataProvider requestDataProvider
     * @param Json $request
     * @param string $map
     * @param array $constraint
     */
    public function testUploadRequest(Json $request, string $map, array $constraint) : void
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5_000);
        $fileProvider = $this->createStub(FileProvider::class);
        $fileProvider->method('getMap')->willReturn(Json::fromString($map));
        $fileProvider->method('getFile')->willReturn($file);
        self::getGraphpinator($fileProvider, $constraint)
            ->run(new JsonRequestFactory($request));

        self::assertTrue(true);
    }

    /**
     * @dataProvider requestInvalidDataProvider
     * @param Json $request
     * @param string $map
     * @param array $constraint
     */
    public function testUploadRequestInvalid(Json $request, string $map, array $constraint, string $exception) : void
    {
        $stream = $this->createStub(StreamInterface::class);
        $stream->method('getContents')->willReturn('test file');
        $stream->method('getMetaData')->willReturn(__DIR__ . '/textFile.txt');
        $file = $this->createStub(UploadedFileInterface::class);
        $file->method('getStream')->willReturn($stream);
        $file->method('getSize')->willReturn(5_000);
        $fileProvider = $this->createStub(FileProvider::class);
        $fileProvider->method('getMap')->willReturn(Json::fromString($map));
        $fileProvider->method('getFile')->willReturn($file);

        $this->expectException($exception);
        $this->expectExceptionMessage(\constant($exception . '::MESSAGE'));

        self::getGraphpinator($fileProvider, $constraint)
            ->run(new JsonRequestFactory($request));
    }

    protected static function getGraphpinator(
        FileProvider $fileProvider,
        array $constraint,
    ) : Graphpinator
    {
        $query = new class ($constraint) extends Type
        {
            protected const NAME = 'Query';

            public function __construct(
                protected array $constraint,
            )
            {
                parent::__construct();
            }

            public function validateNonNullValue($rawValue) : bool
            {
                return true;
            }

            protected function getFieldDefinition() : ResolvableFieldSet
            {
                return new ResolvableFieldSet([
                    ResolvableField::create(
                        'fieldUpload',
                        UploadVarianceTest::getUploadType(),
                        static function ($parent, ?UploadedFileInterface $file) : ?UploadedFileInterface {
                            return $file;
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create(
                            'file',
                            new UploadType(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->constraint),
                    ])),
                    ResolvableField::create(
                        'fieldMultiUpload',
                        UploadVarianceTest::getUploadType()->notNullList(),
                        static function ($parent, array $files) : array {
                            return $files;
                        },
                    )->setArguments(new ArgumentSet([
                        Argument::create(
                            'files',
                            (new UploadType())->list(),
                        )->addDirective(TestSchema::getType('uploadConstraint'), $this->constraint),
                    ])),
                ]);
            }
        };

        return new Graphpinator(
            new Schema(
                new SimpleContainer([
                    'Query' => $query,
                    'UploadType' => UploadVarianceTest::getUploadType(),
                    'Upload' => new UploadType(),
                ], []),
                $query,
            ),
            false,
            new ModuleSet([
                new UploadModule($fileProvider),
            ]),
        );
    }
}
