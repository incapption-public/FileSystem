# FileSystem

This package is an object wrapper for https://github.com/thephpleague/flysystem

## Installation
```bash 
composer require incapption/file-system
```

## Requirements
```bash 
PHP >= 8.1
```

## How to use
### 1. Create your own class
Create your own file class, which extends ```Incapption\FileSystem\File```.
>You always need an adapter, based on the official adapters https://flysystem.thephpleague.com/docs/
> 
#### 1.1 Example for local files
```php
<?php

class MyFile extends Incapption\FileSystem\File
{
    /**
     * @param  string|null  $filePath
     */
    public function __construct(?string $filePath = null)
    {
        $adapter = new LocalFilesystemAdapter(
            'path/to/root' # Path to the root of you application
        );
        
        parent::__construct($adapter, $filePath);
    }
    
    public function myCustomMethod()
    {
        // you can extend your own class with additional methods
    }
}

// examples
$file = new MyFile('public/images/avatar03.jpg');
$file->__delete();

$file = new MyFile();
$file->__write('user/1/101/images/avatar.jpg', file_get_contents('tmp/uploaded_avatar.jpg'));
```

---

#### 1.2 Example for S3 files
```php
<?php

class MyS3File extends Incapption\FileSystem\File
{
    /**
     * @param  string|null  $filePath
     */
    public function __construct(?string $filePath = null)
    {
        $client = new S3Client([
            'version'     => 'latest',
            'region'      => 'us-east-2',
            'endpoint'    => 'https://s3.us-east-2.amazonaws.com',
            'credentials' => [
                'key'    => 'YOUR_API_KEY',
                'secret' => 'YOUR_API_SECRET',
            ],
            'http'        => [
                'timeout'         => 10,
                'connect_timeout' => 10,
            ],
        ]);
    
        $adapter = new League\Flysystem\AwsS3V3\AwsS3V3Adapter(
            $client,
            'your-bucket-name'
        );
        
        parent::__construct($adapter, $filePath);
    }
    
    public function myCustomMethod()
    {
        // you can extend your own class with additional methods
    }
}

// examples
$file = new MyFile('public/images/avatar03.jpg');
$file->__delete();

$file = new MyFile();
$file->__write('user/1/101/images/avatar.jpg', file_get_contents('tmp/uploaded_avatar.jpg'));
```
**Requirements for S3**

**Additional composer package**
```bash
# So you can use AwsS3V3Adapter
composer require league/flysystem-aws-s3-v3:3.34.0
```

**IAM Permissions**\
The required IAM permissions are as followed:
```json
 {
    "Version": "2012-10-17",
    "Statement": [
        {
            "Effect": "Allow",
            "Action": [
                "s3:ListBucket",
                "s3:GetObject",
                "s3:DeleteObject",
                "s3:GetObjectAcl",
                "s3:PutObjectAcl",
                "s3:PutObject"
            ],
            "Resource": [
                "arn:aws:s3:::your-bucket-name",
                "arn:aws:s3:::your-bucket-name/*"
            ]
        }
    ]
}
```

### 2. Public methods
Methods you can use on your file object
```php
public function __construct(FilesystemAdapter $adapter, ?string $filePath = null);

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

/* IN ADDITION:
 * all public function from \League\Flysystem\Filesystem
 */
```

### 3. Testing
```bash
./vendor/bin/phpunit
```

### 4. Exceptions
>FilesystemException are thrown if something went wrong
```php

### 5. Things to know
- If you delete a file, the object gets resetted
- getExtension() returns the Extension without a leading dot
- Methods are throwing exception. If no exception is thrown, everything worked fine