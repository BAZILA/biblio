<?php

namespace Nextend\Framework\Filesystem\Joomla;

use Nextend\Framework\Filesystem\AbstractPlatformFilesystem;

if (class_exists('\Joomla\CMS\Filesystem\Folder')) {
    class JoomlaFolder extends \Joomla\CMS\Filesystem\Folder {

    }
} else {
    class JoomlaFolder extends \Joomla\Filesystem\Folder {

    }
}

if (class_exists('\Joomla\CMS\Filesystem\File')) {
    class JoomlaFile extends \Joomla\CMS\Filesystem\File {

    }
} else {
    class JoomlaFile extends \Joomla\Filesystem\File {

    }
}

class JoomlaFilesystem extends AbstractPlatformFilesystem {

    public function init() {
        $this->_basepath = realpath(JPATH_SITE == '' ? DIRECTORY_SEPARATOR : JPATH_SITE . DIRECTORY_SEPARATOR);
        if ($this->_basepath == DIRECTORY_SEPARATOR) {
            $this->_basepath = '';
        }

        $this->measurePermission($this->_basepath . '/media/');
    }

    public function getWebCachePath() {
        return $this->getBasePath() . '/media/nextend';
    }

    public function getNotWebCachePath() {
        return JPATH_CACHE . '/nextend';
    }

    public function getImagesFolder() {
        if (defined('JPATH_NEXTEND_IMAGES')) {
            return $this->_basepath . JPATH_NEXTEND_IMAGES;
        }

        return $this->_basepath . '/images';
    }

    /**
     * @param $file
     *
     * @return bool
     */
    public function fileexists($file) {
        return file_exists($file);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function folders($path) {
        return JoomlaFolder::folders($path);
    }

    /**
     * @param $path
     *
     * @return bool
     */
    public function is_writable($path) {
        return true;
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function createFolder($path) {
        return JoomlaFolder::create($path, $this->dirPermission);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function deleteFolder($path) {
        return JoomlaFolder::delete($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function existsFolder($path) {
        return @is_dir($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function files($path) {
        return JoomlaFolder::files($path);
    }

    /**
     * @param $path
     *
     * @return mixed
     */
    public function existsFile($path) {
        return file_exists($path);
    }

    /**
     * @param $path
     * @param $buffer
     *
     * @return mixed
     */
    public function createFile($path, $buffer) {
        return JoomlaFile::write($path, $buffer);
    }

}