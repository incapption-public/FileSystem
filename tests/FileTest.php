<?php

namespace Incapption\FileSystem\Tests;

use Incapption\FileSystem\File;
use League\Flysystem\FilesystemException;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\UnableToReadFile;
use PHPUnit\Framework\TestCase;

class FileTest extends TestCase
{
    private LocalFilesystemAdapter $adapter;

    private string $storageDir;

    private string $testStorageDir;

    protected function setUp(): void
    {
        $this->storageDir = dirname(__DIR__) . '/tests/Storage';
        $this->testStorageDir = dirname(__DIR__) . '/tests/TestStorage';

        $this->adapter = new LocalFilesystemAdapter(dirname(__DIR__));
    }

    protected function tearDown(): void
    {
        foreach (glob($this->testStorageDir . '/**/*') as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                unlink($file);
            }
        }

        foreach (glob($this->testStorageDir . '/*') as $file) {
            if (is_file($file) && basename($file) !== '.gitkeep') {
                unlink($file);
            }
        }
    }

    /** @test */
    public function write_a_file(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/test.jpg', file_get_contents($this->storageDir . '/test.jpg'));

        $this->assertFileExists($this->testStorageDir . '/test.jpg');
        $this->assertEquals('test.jpg', $file->getName());
        $this->assertEquals('jpg', $file->getExtension());
        $this->assertTrue($file->getSize() > 0);
    }

    /** @test */
    public function write_a_file_as_stream(): void
    {
        $file = new File($this->adapter, null);
        $file->__writeStream('./tests/TestStorage/5MB_streamed.bin', fopen($this->storageDir . '/5MB.bin', 'r'));

        $this->assertFileExists($this->testStorageDir . '/5MB_streamed.bin');
        $this->assertEquals('5MB_streamed.bin', $file->getName());
        $this->assertTrue($file->getSize() > 0);
    }

    /** @test */
    public function instantiate_a_file(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/test.jpg', file_get_contents($this->storageDir . '/test.jpg'));

        $file = new File($this->adapter, './tests/TestStorage/test.jpg');
        $data = $file->toArray();

        $this->assertEquals('test.jpg', $data['file_name']);
        $this->assertEquals('image/jpeg', $data['file_mime_type']);
        $this->assertEquals('jpg', $data['file_extension']);
        $this->assertNotEmpty($data['full_path']);
        $this->assertNotEmpty($data['file_size']);
        $this->assertNotEmpty($data['file_last_modified']);
        $this->assertNotEmpty($data['directory_name']);
    }

    /** @test */
    public function toJson_returns_valid_json(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/test.jpg', file_get_contents($this->storageDir . '/test.jpg'));

        $file = new File($this->adapter, './tests/TestStorage/test.jpg');
        $json = $file->toJson();

        $this->assertJson($json);
        $decoded = json_decode($json, true);
        $this->assertArrayHasKey('file_name', $decoded);
        $this->assertArrayHasKey('file_size', $decoded);
    }

    /** @test */
    public function move_a_file(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/move_me.bin', file_get_contents($this->storageDir . '/5MB.bin'));

        $file = new File($this->adapter, './tests/TestStorage/move_me.bin');
        $file->__move('./tests/TestStorage/Subfolder/move_me.bin');

        $this->assertFileDoesNotExist($this->testStorageDir . '/move_me.bin');
        $this->assertFileExists($this->testStorageDir . '/Subfolder/move_me.bin');
        $this->assertEquals('./tests/TestStorage/Subfolder/move_me.bin', $file->getFullPath());
        $this->assertStringContainsString('Subfolder', $file->getDirectoryName());
    }

    /** @test */
    public function rename_a_file(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/original.bin', file_get_contents($this->storageDir . '/5MB.bin'));

        $file = new File($this->adapter, './tests/TestStorage/original.bin');
        $file->__rename('renamed.bin');

        $this->assertFileDoesNotExist($this->testStorageDir . '/original.bin');
        $this->assertFileExists($this->testStorageDir . '/renamed.bin');
        $this->assertEquals('renamed.bin', $file->getName());
        $this->assertStringNotContainsString('original.bin', $file->getFullPath());
    }

    /** @test */
    public function copy_a_file(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/source.bin', file_get_contents($this->storageDir . '/5MB.bin'));

        $file = new File($this->adapter, './tests/TestStorage/source.bin');
        $file->__copy('./tests/TestStorage/copy.bin');

        $this->assertFileExists($this->testStorageDir . '/source.bin');
        $this->assertFileExists($this->testStorageDir . '/copy.bin');
        $this->assertEquals('source.bin', $file->getName());

        $copy = new File($this->adapter, './tests/TestStorage/copy.bin');
        $this->assertEquals('copy.bin', $copy->getName());
        $this->assertEquals($file->getSize(), $copy->getSize());
    }

    /** @test */
    public function delete_a_file_returns_true(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/delete_me.bin', file_get_contents($this->storageDir . '/5MB.bin'));

        $file = new File($this->adapter, './tests/TestStorage/delete_me.bin');
        $result = $file->__delete();

        $this->assertTrue($result);
        $this->assertNull($file->getFullPath());
        $this->assertFileDoesNotExist($this->testStorageDir . '/delete_me.bin');
    }

    /** @test */
    public function get_content_returns_file_content(): void
    {
        $originalContent = file_get_contents($this->storageDir . '/test.jpg');

        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/test.jpg', $originalContent);

        $file = new File($this->adapter, './tests/TestStorage/test.jpg');
        $this->assertEquals($originalContent, $file->getContent());
    }

    /** @test */
    public function reading_nonexistent_file_throws_exception(): void
    {
        $this->expectException(FilesystemException::class);

        $file = new File($this->adapter, './tests/TestStorage/does_not_exist.bin');
        $file->getContent();
    }

    /** @test */
    public function get_directory_name_returns_correct_path(): void
    {
        $file = new File($this->adapter, null);
        $file->__write('./tests/TestStorage/Subfolder/nested.bin', file_get_contents($this->storageDir . '/5MB.bin'));

        $file = new File($this->adapter, './tests/TestStorage/Subfolder/nested.bin');
        $this->assertStringContainsString('TestStorage/Subfolder', $file->getDirectoryName());
        $this->assertStringNotContainsString('nested.bin', $file->getDirectoryName());
    }
}
