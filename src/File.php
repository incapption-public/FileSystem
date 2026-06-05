<?php

namespace Incapption\FileSystem;

use Incapption\FileSystem\Interfaces\FileInterface;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemAdapter;
use League\Flysystem\FilesystemException;

class File extends Filesystem implements FileInterface
{
    /**
     * @var string|null
     */
    public $filePath;

    /**
     * @param  FilesystemAdapter  $adapter
     * @param  string|null  $filePath
     */
    public function __construct(FilesystemAdapter $adapter, ?string $filePath = null)
    {
        parent::__construct($adapter);
        $this->filePath = $filePath;
    }

    /**
     * @param  string  $dest
     * @param  string  $contents
     * @return FileInterface
     * @throws FilesystemException
     */
    public function __write(string $dest, $contents): FileInterface
    {
        $this->write($dest, $contents);

        $this->filePath = $dest;

        return $this;
    }

    /**
     * @param  string  $dest
     * @param  resource  $contents
     * @return FileInterface
     * @throws FilesystemException
     */
    public function __writeStream(string $dest, $contents): FileInterface
    {
        $this->writeStream($dest, $contents);

        $this->filePath = $dest;

        return $this;
    }

    /**
     * @param  string  $dest
     * @return FileInterface
     * @throws FilesystemException
     */
    public function __move(string $dest): FileInterface
    {
        $this->writeStream($dest, $this->readStream($this->filePath));
        $this->delete($this->filePath);

        $this->filePath = $dest;

        return $this;
    }

    /**
     * @param  string  $new_name
     * @return FileInterface
     * @throws FilesystemException
     */
    public function __rename(string $new_name): FileInterface
    {
        $newFilePath = $this->getDirectoryName().DIRECTORY_SEPARATOR.$new_name;

        $this->__move($newFilePath);

        return $this;
    }

    /**
     * @param  string  $dest
     * @return FileInterface
     * @throws FilesystemException
     */
    public function __copy(string $dest): FileInterface
    {
        $this->writeStream($dest, $this->readStream($this->filePath));

        return $this;
    }

    /**
     * @return bool
     * @throws FilesystemException
     */
    public function __delete(): bool
    {
        $this->delete($this->filePath);

        if($this->fileExists($this->filePath))
            return false;

        $this->filePath = null;

        return true;
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    public function getContent(): string
    {
        return $this->read($this->filePath);
    }

    /**
     * @return string
     */
    public function getFullPath(): ?string
    {
        return $this->filePath;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return basename($this->filePath);
    }

    /**
     * @return int
     * @throws FilesystemException
     */
    public function getSize(): int
    {
        return $this->fileSize($this->filePath);
    }

    /**
     * @return string
     */
    public function getExtension(): string
    {
        return pathinfo($this->getName(), PATHINFO_EXTENSION);
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    public function getMimeType(): string
    {
        return $this->mimeType($this->filePath);
    }

    /**
     * @return int
     * @throws FilesystemException
     */
    public function getLastModified(): int
    {
        return $this->lastModified($this->filePath);
    }

    /**
     * @return string
     */
    public function getDirectoryName(): string
    {
        return dirname($this->getFullPath());
    }

    /**
     * @return array
     * @throws FilesystemException
     */
    public function toArray(): array
    {
        return array(
            'file_name'          => $this->getName(),
            'file_size'          => $this->getSize(),
            'file_extension'     => $this->getExtension(),
            'file_mime_type'     => $this->getMimeType(),
            'file_last_modified' => $this->getLastModified(),
            'full_path'          => $this->getFullPath(),
            'directory_name'     => $this->getDirectoryName()
        );
    }

    /**
     * @return string
     * @throws FilesystemException
     */
    public function toJson(): string
    {
        return json_encode($this->toArray());
    }
}
