<?php

namespace Incapption\FileSystem\Interfaces;

interface FileInterface
{
    public function __write(string $dest, $contents): FileInterface;

    public function __writeStream(string $dest, $contents): FileInterface;

    public function __move(string $dest): FileInterface;

    public function __rename(string $new_name): FileInterface;

    public function __copy(string $dest): FileInterface;

    public function __delete(): bool;

    public function getContent(): string;

    public function getFullPath(): ?string;

    public function getName(): string;

    public function getSize(): int;

    public function getExtension(): string;

    public function getMimeType(): string;

    public function getLastModified(): int;

    public function getDirectoryName(): string;

    public function toArray(): array;

    public function toJson(): string;
}