<?php

namespace U89Man\Filesystem;

use FilesystemIterator;
use InvalidArgumentException;

class Fs
{
    /**
     * Типы хешей.
     */
    const HASH_MD5  = 'md5';
    const HASH_SHA1 = 'sha1';

    /**
     * Права доступа к файлам и каталогам.
     */
    const MODE_FILE_PRIVATE = 0600;
    const MODE_FILE_PUBLIC = 0644;
    const MODE_DIRECTORY_PRIVATE = 0700;
    const MODE_DIRECTORY_PUBLIC = 0755;


    /**
     * Указатель на текущий экземпляр класса.
     *
     * @var Fs
     */
    protected static $instance;


    /**
     * Получает экземпляр класса.
     *
     * @return Fs
     */
    public static function getInstance()
    {
        if (static::$instance == null) {
            static::$instance = new static();
        }

        return static::$instance;
    }

    /**
     * Устанавливает/обновляет время доступа и модификации файла.
     * Если файл или каталог не существует, будет создан пустой файл по указанному пути.
     *
     * @param string $path
     * @param int|null $time
     *
     * @return bool
     */
    public function touch($path, $time = 0)
    {
        return touch($path, $time > 0 ? $time : time());
    }

    /**
     * Создает файл.
     *
     * @param string $path
     * @param int $mode 0xxx
     *
     * @return bool
     */
    public function createFile($path, $mode = self::MODE_FILE_PUBLIC)
    {
        return $this->writeFile($path, '', true) && $this->chmod($path, $mode);
    }

    /**
     * Создает каталог.
     *
     * @param string $path
     * @param int $mode 0xxx
     * @param bool $recursive
     *
     * @return bool
     */
    public function createDir($path, $mode = self::MODE_DIRECTORY_PUBLIC, $recursive = false)
    {
        return mkdir($path, $mode, $recursive);
    }

    /**
     * Читает содержимое из файла в строку.
     *
     * @param string $path
     * @param bool $lock
     *
     * @return string
     */
    public function readFile($path, $lock = false)
    {
        $content = null;

        if ($lock) {
            if ($f = fopen($path, 'rb')) {
                try {
                    if (flock($f, LOCK_SH)) {
                        clearstatcache(true, $path);

                        $content = fread($f, $this->size($path) ?: 1);

                        flock($f, LOCK_UN);
                    }
                } finally {
                    fclose($f);
                }
            }
        } else {
            $content = file_get_contents($path);
        }

        if ($content) {
            return $content;
        }

        return '';
    }

    /**
     * Читает содержимое файла построчно и помещает его в массив.
     *
     * @param string $path
     * @param bool $skipEmpty
     *
     * @return array
     */
    public function readFileLines($path, $skipEmpty = false)
    {
        return file($path, ($skipEmpty ? FILE_SKIP_EMPTY_LINES : 0) | FILE_IGNORE_NEW_LINES);
    }

    /**
     * Обеспечивает существование каталога.
     *
     * @param string $path
     *
     * @return void
     */
    protected function ensureDirectoryExists($path)
    {
        $dir = dirname($path);

        if ($this->isDir($dir)) {
            return;
        }

        $this->createDir($dir, self::MODE_DIRECTORY_PUBLIC,true);
    }

    /**
     * Записывает содержимое в файл.
     *
     * @param string $path
     * @param string $content
     * @param bool $lock
     *
     * @return bool
     */
    public function writeFile($path, $content, $lock = false)
    {
        $this->ensureDirectoryExists($path);

        return file_put_contents($path, $content, ($lock ? LOCK_EX : 0)) !== false;
    }

    /**
     * Записывает содержимое в конец файла.
     *
     * @param string $path
     * @param string $content
     * @param bool $lock
     *
     * @return bool
     */
    public function appendFile($path, $content, $lock = false)
    {
        $this->ensureDirectoryExists($path);

        return file_put_contents($path, $content, (($lock ? LOCK_EX : 0 ) | FILE_APPEND)) !== false;
    }

    /**
     * Записывает содержимое в начало файла.
     *
     * @param string $path
     * @param string $content
     * @param bool $lock
     *
     * @return bool
     */
    public function prependFile($path, $content, $lock = false)
    {
        $content = $content.($this->exists($path) ? $this->readFile($path, $lock) : '');

        return $this->writeFile($path, $content, $lock);
    }

    /**
     * Получает размер файла или каталога.
     *
     * @param string $path
     *
     * @return int
     */
    public function size($path)
    {
        $size = 0;

        if ($this->isDir($path)) {
            foreach ($this->listDir($path) as $file) {
                $size += $this->size($path.'/'.$file);
            }
        } elseif ($this->isFile($path)) {
            $size = filesize($path);
        }

        return (int) $size;
    }

    /**
     * Получает содержимое каталога в виде массива имен файлов и каталогов.
     *
     * @param string $path
     * @param bool $onlyFiles
     * @param string|string[]|null $extensions
     *
     * @return array
     */
    public function listDir($path, $onlyFiles = false, $extensions = null)
    {
        if (! is_null($extensions)) {
            $extensions = is_array($extensions) ? $extensions : array($extensions);
        }

        $files = [];

        foreach (new FilesystemIterator($path) as $item) {
            if ($onlyFiles) {
                if ($item->isDir()) {
                    continue;
                }

                if (! is_null($extensions) && ! in_array($item->getExtension(), $extensions)) {
                    continue;
                }
            }
            $files[] = $item->getFileName();
        }

        return $files;
    }

    /**
     * Получает хеш файла.
     *
     * @param string $path
     * @param string $type
     *
     * @return false|string
     */
    public function hashFile($path, $type = self::HASH_MD5)
    {
        switch ($type) {
            case self::HASH_SHA1:
                return sha1_file($path);
            case self::HASH_MD5:
            default:
                return md5_file($path);
        }
    }

    /**
     * Переименовывает файл или каталог.
     *
     * @param string $fromPath
     * @param string $toPath
     *
     * @return bool
     */
    public function rename($fromPath, $toPath)
    {
        return rename($fromPath, $toPath);
    }

    /**
     * Удаляет файл или каталог.
     *
     * @param string $path
     * @param bool $recursive
     *
     * @return bool
     */
    public function delete($path, $recursive = false)
    {
        if ($this->isDir($path)) {
            $listDir = $this->listDir($path);

            if (! $recursive && ! empty($listDir)) {
                return false;
            }

            foreach (array_reverse($listDir) as $item) {
                $this->delete($path.'/'.$item, $recursive);
            }

            return rmdir($path);
        }

        return unlink($path);
    }

    /**
     * Копирует файл или каталог.
     *
     * @param string $fromPath
     * @param string $toPath
     * @param bool $replace
     *
     * @return bool
     */
    protected function _copy($fromPath, $toPath, $replace = false)
    {
        if ($this->exists($toPath) && ! $replace) {
            return false;
        }

        if ($this->isFile($fromPath)) {
            copy($fromPath, $toPath);
        }
        elseif ($this->isDir($fromPath)) {
            if ($this->exists($toPath)) {
                $this->delete($toPath, true);
            }

            $this->createDir($toPath);

            foreach ($this->listDir($fromPath) as $path) {
                if (! $this->_copy($fromPath.'/'.$path, $toPath.'/'.$path, $replace)) {
                    return false;
                }
            }
        }

        $this->chmod($toPath, $this->chmod($fromPath));

        return $this->size($fromPath) === $this->size($toPath);
    }

    /**
     * Копирует файл или каталог.
     *
     * @param string $fromPath
     * @param string $toPath
     * @param bool $replace
     *
     * @return bool
     */
    public function copy($fromPath, $toPath, $replace = false)
    {
        return $this->_copy($fromPath, $toPath, $replace);
    }

    /**
     * Перемещает файл или каталог.
     *
     * @param $fromPath
     * @param $toPath
     * @param bool $replace
     *
     * @return bool
     */
    public function move($fromPath, $toPath, $replace = false)
    {
        return $this->_copy($fromPath, $toPath, $replace) && $this->delete($fromPath, true);
    }

    /**
     * Получает MIME-тип файла.
     *
     * @param string $path
     *
     * @return mixed
     */
    public function mimetype($path)
    {
        return mime_content_type($path);
    }

    /**
     * Получает или устанавливает права доступа к файлу или каталогу.
     *
     * @param string $path
     * @param int $mode 0xxx
     *
     * @return bool|string
     */
    public function chmod($path, $mode = 0)
    {
        if ($mode > 0) {
            return chmod($path, intval($mode, 8));
        }

        return substr(decoct(fileperms($path)), -4);
    }

    /**
     * Получает тип файла.
     * Возможными значениями являются fifo, char, dir, block, link, file, socket и unknown.
     *
     * @param string $path
     *
     * @return false|string
     */
    public function type($path)
    {
        return filetype($path);
    }
    
    /**
     * Проверяет существование указанного файла или каталога.
     *
     * @param string $path
     *
     * @return bool
     */
    public function exists($path)
    {
        return file_exists($path);
    }

    /**
     * Определяет, является ли указанный путь файлом.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isFile($path)
    {
        return is_file($path);
    }

    /**
     * Определяет, является ли указанный путь каталогом.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isDir($path)
    {
        return is_dir($path);
    }

    /**
     * Определяет доступность файла или каталога для чтения.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isReadable($path)
    {
        return is_readable($path);
    }

    /**
     * Определяет доступность файла или каталога для записи.
     *
     * @param string $path
     *
     * @return bool
     */
    public function isWritable($path)
    {
        return is_writable($path);
    }

    /**
     * Получает время последнего доступа к файлу или каталогу.
     *
     * @param string $path
     *
     * @return int
     */
    public function atime($path)
    {
        return (int) fileatime($path);
    }

    /**
     * Получает время изменения индексного дескриптора файла или каталога.
     *
     * @param string $path
     *
     * @return int
     */
    public function ctime($path)
    {
        return (int) filectime($path);
    }

    /**
     * Получает время последнего изменения файла или каталога.
     *
     * @param string $path
     *
     * @return int
     */
    public function mtime($path)
    {
        return (int) filemtime($path);
    }

    /**
     * Проверяет, коректность пути к файлу с расширением ".php".
     *
     * @param string $path
     *
     * @return void
     */
    protected function ensurePhpFile($path)
    {
        if (strtolower(substr($path, -4)) != '.php') {
            throw new InvalidArgumentException('Не корректный тип файла.');
        }
    }

    /**
     * Экспортирует массив данных в PHP файл.
     *
     * @param string $path
     * @param array $data
     *
     * @return void
     */
    public function export($path, array $data)
    {
        $this->ensurePhpFile($path);
        $this->writeFile($path, '<?php return '.var_export($data, true).';', true);
    }
}
