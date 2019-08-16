<?php

namespace VysokeSkoly\Tests\ImageApi\Facade;

use Mockery as m;
use phpmock\mockery\PHPMockery;
use Symfony\Component\Filesystem\Exception\IOException;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use VysokeSkoly\ImageApi\Facade\StorageFacade;
use VysokeSkoly\ImageApi\Service\NamespaceService;
use VysokeSkoly\Tests\ImageApi\AbstractTestCase;

class StorageFacadeTest extends AbstractTestCase
{
    const STORAGE_PATH = __DIR__ . '/../Fixtures/';
    const DEFAULT_NAMESPACE = 'default';

    const EXPECTED_UPLOAD_PATH = __DIR__ . '/../Fixtures/' . self::DEFAULT_NAMESPACE . '/';

    /** @var StorageFacade */
    private $storage;
    /** @var Filesystem|m\MockInterface */
    private $fileSystem;

    public function setUp()
    {
        $this->fileSystem = m::mock(Filesystem::class);
        $namespaceService = new NamespaceService(new RequestStack(), self::DEFAULT_NAMESPACE);

        $this->storage = new StorageFacade(self::STORAGE_PATH, $namespaceService, $this->fileSystem);
    }

    public function testShouldGetNoFileStatus()
    {
        $emptyFileBag = new FileBag([]);
        $expectedStatus = [
            'status' => 'NO_FILES',
            'isSuccess' => false,
            'messages' => [],
        ];

        $this->storage->saveFiles($emptyFileBag);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
    }

    public function testShouldSaveUploadedFilesAndGetStatus()
    {
        $fileName = 'file';
        $expectedStatus = [
            'status' => 'OK',
            'isSuccess' => true,
            'messages' => [
                $fileName,
            ],
        ];

        $file = new File(self::EXPECTED_UPLOAD_PATH . $fileName);

        $uploadedFile = $this->mockUploadedFile($fileName);
        $uploadedFile->expects($this->once())
            ->method('move')
            ->with(self::EXPECTED_UPLOAD_PATH, $fileName)
            ->willReturn($file);

        $files = new FileBag([$uploadedFile]);

        $this->storage->saveFiles($files);

        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
        $this->assertTrue($status->isSuccess());
    }

    /**
     * @return UploadedFile|\PHPUnit_Framework_MockObject_MockObject
     */
    private function mockUploadedFile(string $fileName)
    {
        $uploadedFile = $this->createMock(UploadedFile::class);

        $uploadedFile->expects($this->once())
            ->method('getClientOriginalName')
            ->willReturn($fileName);

        return $uploadedFile;
    }

    public function testShouldGetErrorStatus()
    {
        $errorMessage = 'error - message';
        $fileName = 'fileName';
        $expectedStatus = [
            'status' => 'ERROR',
            'isSuccess' => false,
            'messages' => [
                'Exception',
                $errorMessage,
            ],
        ];

        $uploadedFile = $this->mockUploadedFile($fileName);
        $uploadedFile->expects($this->once())
            ->method('move')
            ->with(self::EXPECTED_UPLOAD_PATH, $fileName)
            ->willThrowException(new \Exception($errorMessage));

        $files = new FileBag([$uploadedFile]);

        $this->storage->saveFiles($files);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
    }

    public function testShouldDeleteFile()
    {
        $fileName = 'file-to-delete';
        $filePath = self::EXPECTED_UPLOAD_PATH . $fileName;
        $expectedStatus = [
            'status' => 'OK',
            'isSuccess' => true,
            'messages' => [$fileName],
        ];

        $this->fileSystem->shouldReceive('exists')
            ->with($filePath)
            ->once()
            ->andReturn(true);
        $this->fileSystem->shouldReceive('remove')
            ->with($filePath)
            ->once();

        $this->storage->delete($fileName);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
    }

    public function testShouldReturnNotFoundStatusOnDelete()
    {
        $fileName = 'file-to-delete';
        $filePath = self::EXPECTED_UPLOAD_PATH . $fileName;
        $expectedStatus = [
            'status' => 'ERROR',
            'isSuccess' => false,
            'messages' => [
                NotFoundHttpException::class,
                sprintf("File '%s' was not found.", $fileName),
            ],
        ];

        $this->fileSystem->shouldReceive('exists')
            ->with($filePath)
            ->once()
            ->andReturn(false);
        $this->fileSystem->shouldNotReceive('remove');

        $this->storage->delete($fileName);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
        $this->assertSame(404, $status->getStatusCode());
    }

    public function testShouldReturnErrorStatusOnDelete()
    {
        $fileName = 'file-to-delete';
        $filePath = self::EXPECTED_UPLOAD_PATH . $fileName;
        $errorMessage = 'error-message';
        $expectedStatus = [
            'status' => 'ERROR',
            'isSuccess' => false,
            'messages' => [
                IOException::class,
                $errorMessage,
            ],
        ];

        $this->fileSystem->shouldReceive('exists')
            ->with($filePath)
            ->once()
            ->andReturn(true);
        $this->fileSystem->shouldReceive('remove')
            ->once()
            ->andThrow(new IOException($errorMessage));

        $this->storage->delete($fileName);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
        $this->assertSame(500, $status->getStatusCode());
    }

    public function testShouldListAllFromStorage()
    {
        $expectedFiles = ['file'];

        $list = $this->storage->listAll();

        $this->assertSame($expectedFiles, $list);
    }

    public function testShouldGetImage()
    {
        $fileName = 'file';
        $content = 'content';
        $filePath = self::EXPECTED_UPLOAD_PATH . $fileName;
        $expectedStatus = [
            'status' => 'OK',
            'isSuccess' => true,
            'messages' => [$fileName],
        ];

        $this->fileSystem->shouldReceive('exists')
            ->with($filePath)
            ->once()
            ->andReturn(true);

        PHPMockery::mock('VysokeSkoly\ImageApi\Facade', 'file_get_contents')
            ->with($filePath)
            ->once()
            ->andReturn($content);

        $result = $this->storage->getImage($fileName);
        $status = $this->storage->getStatus();

        $this->assertSame($content, $result);
        $this->assertSame($expectedStatus, $status->toArray());
    }

    public function testShouldNotFindImage()
    {
        $fileName = 'file';
        $content = 'content';
        $filePath = self::EXPECTED_UPLOAD_PATH . $fileName;
        $errorMessage = sprintf('File \'%s\' was not found.', $fileName);
        $expectedStatus = [
            'status' => 'ERROR',
            'isSuccess' => false,
            'messages' => [
                NotFoundHttpException::class,
                $errorMessage,
            ],
        ];

        $this->fileSystem->shouldReceive('exists')
            ->with($filePath)
            ->once()
            ->andReturn(false);

        $result = $this->storage->getImage($fileName);
        $status = $this->storage->getStatus();

        $this->assertNull($result);
        $this->assertSame($expectedStatus, $status->toArray());
    }
}
