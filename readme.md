## Filesystem 0.2.2

##### Установка

```
composer require u89man/filesystem
```

##### Примеры

```php
$fs = new \U89Man\Filesystem\Fs();
```

```php
// Обновляет время последнего доступа к файлу
$fs->touch('file.txt'); // bool

// Обновляет время последнего доступа к файлу на указанное
$fs->touch('file.txt', 1596297826); // bool

// Обновляет время последнего доступа к каталогу 
$fs->touch('/dir'); // bool

// Обновляет время последнего доступа к каталогу на указанное
$fs->touch('/dir', 1596297826); // bool

// Создает новый файл, если указанный файл или каталог не существует
$fs->touch('non_existent_file.txt'); // bool
```

```php
// Создает новый файл
$fs->createFile('file.txt'); // bool

// Создает новый файл с указанием прав доступа
$fs->createFile('file.txt', 0644); // bool
$fs->createFile('file.txt', Fs::MODE_FILE_PRIVATE); // bool
$fs->createFile('file.txt', Fs::MODE_FILE_PUBLIC); // bool
```

```php
// Создает новый каталог
$fs->createDir('/dir'); // bool

// Создает новый каталог с указанием прав доступа
$fs->createDir('/dir', 0755); // bool
$fs->createDir('/dir', Fs::MODE_DIRECTORY_PRIVATE); // bool
$fs->createDir('/dir', Fs::MODE_DIRECTORY_PUBLIC); // bool
```

```php
// Читает содержимое файла в строку
$content = $fs->readFile('file.txt'); // string

// Читает содержимое файла в строку, блокируя доступ к файлу на время чтения
$content = $fs->readFile('file.txt', true); // string
```

```php
// Построчно читает содержимое файла в массив
$linesContent = $fs->readFileLines('file.txt'); // array

// Построчно читает содержимое файла в массив, пропуская пустые строки
$linesContent = $fs->readFileLines('file.txt', true); // array
```

```php
// Записывает содержимое в файл
$fs->writeFile('file.txt', $content); // bool

// Записывает содержимое в файл, блокируя доступ к файлу на время записи
$fs->writeFile('file.txt', $content, true); // bool
```

```php
// Дописывает содержимое в конец файла
$fs->appendFile('file.txt', $appendContent); // bool

// Дописывает содержимое в конец файла, блокируя доступ к файлу на время записи
$fs->appendFile('file.txt', $appendContent, true); // bool
```

```php
// Дописывает содержимое в начало файла
$fs->prependFile('file.txt', $prependContent); // bool

// Дописывает содержимое в начало файла, блокируя доступ к файлу на время записи
$fs->prependFile('file.txt', $prependContent, true); // bool
```

```php
// Получает размер файла (в байтах)
$size = $fs->size('file.txt'); // int

// Получает размер каталога (в байтах)
$size = $fs->size('/dir'); // int
```

```php
// Получает хеш файла (md5)
$hash = $fs->hashFile('file.txt'); // string

// Получает хеш файла (md5)
$hash = $fs->hashFile('file.txt', Fs::HASH_MD5); // string

// Получает хеш файла (sha1)
$hash = $fs->hashFile('file.txt', Fs::HASH_SHA1); // string
```

```php
// Получает содержимое каталога в виде массива имен (файлы и каталоги)
$listDir = $fs->listDir('/dir'); // array

// Получает содержимое каталога в виде массива имен (только файлы)
$listDir = $fs->listDir('/dir', true); // array

// Получает содержимое каталога в виде массива имен (только файлы с конкретным расширением)
$listDir = $fs->listDir('/dir', true, 'txt'); // array

// Получает содержимое каталога в виде массива имен (только файлы с конкретными расширениями)
$listDir = $fs->listDir('/dir', true, ['js', 'css']); // array
```

```php
// Переименовывает файл
$fs->rename('from.txt', 'to.txt'); // bool

// Переименовывает каталог
$fs->rename('/dir_from', '/dir_to'); // bool
```

```php
// Копирует содержимое файла
$fs->copy('from.txt', 'to.txt'); // bool

// Копирует содержимое файла с заменой 
$fs->copy('from.txt', 'to.txt', true); // bool

// Копирует содержимое каталога
$fs->copy('/dir_from', '/dir_to'); // bool

// Копирует содержимое каталога c заменой
$fs->copy('/dir_from', '/dir_to', true); // bool
```

```php
// Перемещает файл 
$fs->move('from.txt', 'to.txt'); // bool

// Перемещает файл с заменой
$fs->move('from.txt', 'to.txt', true); // bool

// Перемещает каталог
$fs->move('/dir_from', '/dir_to'); // bool

// Перемещает каталог с заменой
$fs->move('/dir_from', '/dir_to', true); // bool
```

```php
// Удаляет файл
$fs->delete('file.txt'); // bool

// Удаляет каталог
$fs->delete('/dir'); // bool

// Удаляет каталог со всем содержимым
$fs->delete('/dir', true); // bool
```

```php
// Получает Mimetype файла
$mimetype = $fs->mimetype('file.txt'); // string "text/plain"
$mimetype = $fs->mimetype('/dir'); // string "directory"
```

```php
// Устанавливает права доступа на файл
$fs->chmod('file.txt', 0644); // bool
$fs->chmod('file.txt', Fs::MODE_FILE_PRIVATE); // bool
$fs->chmod('file.txt', Fs::MODE_FILE_PUBLIC); // bool

// Устанавливает права доступа каталог
$fs->chmod('/dir', 0755); // bool
$fs->chmod('/dir', Fs::MODE_DIRECTORY_PRIVATE); // bool
$fs->chmod('/dir', Fs::MODE_DIRECTORY_PUBLIC); // bool

// Получает права доступа к файлу
$chmod = $fs->chmod('file.txt'); // string

// Получает права доступа к каталогу
$chmod = $fs->chmod('/dir'); // string
```

```php
// Получает тип файла
$type = $fs->type('file.txt'); // string "file"
$type = $fs->type('/dir'); // string "dir"
```

```php
// Проверяет существование файла
$fs->exists('file.txt'); // bool

// Проверяет существование каталога
$fs->exists('/dir'); // bool
```

```php
// Проверяет является ли переданный путь файлом
$fs->isFile('file.txt'); // bool
```

```php
// Проверяет является ли переданный путь каталогом
$fs->isDir('/dir'); // bool
```

```php
// Проверяет доступность файла для чтения
$fs->isReadable('file.txt'); // bool

// Проверяет доступность каталога для чтения
$fs->isReadable('/dir'); // bool
```

```php
// Проверяет доступность файла для записи
$fs->isWritable('file.txt'); // bool

// Проверяет доступность каталога для записи
$fs->isWritable('/dir'); // bool
```

```php
// Получает время последнего доступа к файлу
$time = $fs->atime('file.txt'); // int

// Получает время последнего доступа к каталогу
$time = $fs->atime('/dir'); // int
```

```php
// Получает время последнего изменения индексного дескриптора файла
$time = $fs->ctime('file.txt'); // int

// Получает время последнего изменения индексного дескриптора каталога
$time = $fs->ctime('/dir'); // int
```

```php
// Получает время последней модификации файла
$time = $fs->mtime('file.txt'); // int

// Получает время последней модификации каталога
$time = $fs->mtime('/dir'); // int
```

```php
// Экспортирует массив данных в PHP файл.
$fs->export('file.php', [
    13,
    false,
    28,   
    'string'
]); 
```

```php
// Импортирует массив данных из PHP файла.
$data = $fs->import('file.php'); // array
```
