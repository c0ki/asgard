<?php

namespace Core\CoreBundle\Component\File;

use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\File as BaseFile;

class File extends BaseFile
{

    /**
     * Constructs a new file from the given path.
     * @param string $path The path to the file
     * @param bool   $checkPath Whether to check the path or not
     *
     * @throws FileNotFoundException If the given path is not a file
     */
    public function __construct($path, $checkPath = true) {
        if (preg_match('#^ssh://(.*)$#', $path, $matches)) {
            $path = 'ssh2.sftp://' . $matches[1];
        }
        elseif (preg_match('#^http(s)?://#', $path, $matches)) {
            $checkPath = false;
        }
        if ($checkPath && !file_exists($path)) {
            throw new FileNotFoundException($path);
        }
        parent::__construct($path, false);
        $this->setInfoClass(__CLASS__);
    }

    /**
     * @param null $mask
     * @return File[]
     */
    public function listFiles($mask = null) {
        if (!$this->isReadable()) {
            throw new AccessDeniedException($this->getPathname());
        }
        elseif (!$this->isDir()) {
            throw new FileException("The file {$this->getPathname()} is not a directory");
        }

        if (preg_match('#^http(s)?://#', $this->getPath())) {
            $content = file_get_contents($this->getPathname());
            if (preg_match_all('/href="([^"]+)"/i', $content, $matches)) {
                $files = array();
                foreach ($matches[1] as $file) {
                    $filename = strrchr($file, '/');
                    $dirname = substr($file, 0, -1 * strlen($filename));
                    if ($dirname == parse_url($this->getPathname(), PHP_URL_PATH)) {
                        $files[] = substr($filename, 1);
                    }
                    else {
                        $files[] = $file;
                    }
                }
            }
        }
        else {
            $files = scandir($this->getPathname());
        }
        $files = array_values(array_filter($files,
            function ($filename) use ($mask) {
                if (!empty($mask)) return preg_match("/{$mask}/", $filename);

                return ($filename != '.' && $filename != '..');
            }));
        array_walk($files,
            function (&$file) {
                $file = new self($this->getPathname() . DIRECTORY_SEPARATOR . $file);
                if (preg_match('#^http(s)?://#', $this->getPath())) {
                    return;
                }
                if (!$file->isReadable() || !$file->isFile()) {
                    $file = null;
                }
            });
        $files = array_values(array_filter($files));

        return $files;
    }

    /**
     * @param null $mask
     * @return File[]
     */
    public function listSubDirs($mask = null) {
        if (!$this->isReadable()) {
            throw new AccessDeniedException($this->getPathname());
        }
        elseif (!$this->isDir()) {
            throw new FileException("The file {$this->getPathname()} is not a directory");
        }
        $dirs = scandir($this->getPathname());
        $dirs = array_values(array_filter($dirs,
            function ($filename) use ($mask) {
                if (!empty($mask)) return preg_match("/{$mask}/", $filename);

                return ($filename != '.' && $filename != '..');
            }));
        array_walk($dirs,
            function (&$dir) {
                $dir = new self($this->getPathname() . DIRECTORY_SEPARATOR . $dir);
                if (!$dir->isReadable() || !$dir->isDir()) {
                    $dir = null;
                }
            });
        $dirs = array_values(array_filter($dirs));

        return $dirs;
    }

    public function getSize($humanReadable = false) {
        if (!$this->isReadable()) {
            throw new AccessDeniedException($this->getPathname());
        }
        elseif ($this->isDir()) {
            throw new FileException("The file {$this->getPathname()} is a directory");
        }
        $size = parent::getSize();
        if ($humanReadable) {
            $units = array('K', 'M', 'G');
            $unit = null;
            while ($size > 1024) {
                $size = $size / 1024;
                $unit = array_shift($units);
            }
            if ($unit) {
                $size = number_format($size, 1) . $unit;
            }
        }

        return $size;
    }

    /**
     * return bool
     */
    public function isReadable() {
        return true;
//        Stream error, always return false :-(
//        return parent::isReadable();
    }

    public function isDir() {
        if (preg_match('/^http/', $this->getPath())) {
            return true;
        }
        return parent::isDir();
    }

    protected function getTargetFile($directory, $name = null) {
        $target = parent::getTargetFile($directory, $name);

        return new self($target->getPath() . '/' . $target->getFilename(), false);
    }

    public function copy($directory, $name = null) {
        $target = $this->getTargetFile($directory, $name);

        if (!@copy($this->getPathname(), $target)) {
            $error = error_get_last();
            throw new FileException(sprintf('Could not copy the file "%s" to "%s" (%s)',
                                            $this->getPathname(),
                                            $target,
                                            strip_tags($error['message'])));
        }
        @chmod($target, 0666 & ~umask());

        return $target;
    }

    public function unlink() {
        $filePath = "{$this->getPath()}/{$this->getFilename()}";
        unlink($filePath);
        unset($this);
    }

}
