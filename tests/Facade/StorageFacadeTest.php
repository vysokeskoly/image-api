<?php

namespace VysokeSkoly\Tests\ImageApi\Facade;

use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\FileBag;
use VysokeSkoly\ImageApi\Facade\StorageFacade;
use VysokeSkoly\Tests\ImageApi\AbstractTestCase;

class StorageFacadeTest extends AbstractTestCase
{
    const UPLOAD_PATH = '/tests/';

    /** @var StorageFacade */
    private $storage;

    public function setUp()
    {
        $this->storage = new StorageFacade(self::UPLOAD_PATH);
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

        $file = new File(__DIR__ . '/../Fixtures/' . $fileName);

        $uploadedFile = $this->mockUploadedFile($fileName);
        $uploadedFile->expects($this->once())
            ->method('move')
            ->with(self::UPLOAD_PATH, $fileName)
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
            ->with(self::UPLOAD_PATH, $fileName)
            ->willThrowException(new \Exception($errorMessage));

        $files = new FileBag([$uploadedFile]);

        $this->storage->saveFiles($files);
        $status = $this->storage->getStatus();

        $this->assertSame($expectedStatus, $status->toArray());
    }
}
