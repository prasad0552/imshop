<?php

namespace im\filesystem\components;

use im\filesystem\models\DbFile;
use im\filesystem\models\File;
use yii\base\Behavior;
use yii\base\UnknownMethodException;
use yii\db\ActiveRecord;
use yii\web\UploadedFile;
use Yii;

/**
 * Class FilesBehavior adds possibility to handle file uploads and link them to the model.
 *
 * @property ActiveRecord $owner
 * @package im\filesystem\components
 */
class FilesBehavior extends Behavior
{
    /**
     * @var StorageConfig[]
     */
    public $attributes = [];

    /**
     * @var string default storage config class
     */
    public $storageConfigClass = 'im\filesystem\components\StorageConfig';

    /**
     * @var UploadedFile[]
     */
    private $_uploadedFiles = [];

    /**
     * @var FileInterface[]
     */
    private $_files = [];

    /**
     * @var FilesystemComponent
     */
    private $_filesystemComponent;

    /**
     * @inheritdoc
     */
    public function events()
    {
        return [
            ActiveRecord::EVENT_BEFORE_VALIDATE => 'beforeValidate',
            ActiveRecord::EVENT_AFTER_INSERT => 'afterSave',
            ActiveRecord::EVENT_AFTER_UPDATE => 'afterSave',
//            ActiveRecord::EVENT_BEFORE_DELETE => 'beforeDelete',
        ];
    }

    /**
     * Handles before validate event of owner.
     * Creates instances of uploaded files.
     */
    public function beforeValidate()
    {
        if ($this->hasUploadedFiles()) {
            $this->normalizeAttributes();
            $this->getUploadedFileInstances();
        }
    }

    /**
     * Handles after update event of owner.
     * Saves uploaded files to filesystem.
     */
    public function afterSave()
    {
        $this->saveUploadedFiles();
    }

    /**
     * @inheritdoc
     */
    public function canSetProperty($name, $checkVars = true)
    {
        if (parent::canSetProperty($name, $checkVars)) {
            return true;
        } else {
            return $this->hasAttribute($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __set($name, $value)
    {
        try { parent::__set($name, $value); }
        catch (\Exception $e) {
            $this->setAttribute($name, $value);
        }
    }

    /**
     * @inheritdoc
     */
    public function canGetProperty($name, $checkVars = true)
    {
        if (parent::canGetProperty($name, $checkVars)) {
            return true;
        } else {
            return $this->hasAttribute($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __get($name)
    {
        try { return parent::__get($name); }
        catch (\Exception $e) {
            return isset($this->_uploadedFiles[$name]) ? $this->_uploadedFiles[$name] : null;
        }
    }

    /**
     * @inheritdoc
     */
    public function hasMethod($name)
    {
        if (parent::hasMethod($name)) {
            return true;
        } else {
            if (strncmp($name, 'get', 3) === 0) {
                $name = substr($name, 3);
            }
            return $this->getRelation($name);
        }
    }

    /**
     * @inheritdoc
     */
    public function __call($name, $params)
    {
        if (strncmp($name, 'get', 3) === 0) {
            $name = substr($name, 3);
        }
        if ($relation = $this->getRelation($name)) {
            return $relation;
        }

        throw new UnknownMethodException('Calling unknown method: ' . get_class($this) . "::$name()");
    }

    /**
     * Creates uploaded file instances.
     */
    protected function getUploadedFileInstances()
    {
        foreach ($this->attributes as $attribute => $storageConfig) {
            $this->_uploadedFiles[$attribute] = $storageConfig->multiple
                ? UploadedFile::getInstances($this->owner, $attribute)
                : UploadedFile::getInstance($this->owner, $attribute);
            if (empty($this->_uploadedFiles[$attribute])) {
                $this->_uploadedFiles[$attribute] = $storageConfig->multiple
                    ? UploadedFile::getInstancesByName($attribute)
                    : UploadedFile::getInstanceByName($attribute);
            }
        }
    }

    /**
     * Deletes owner related files if new ones were uploaded.
     */
    protected function deleteRelatedFiles()
    {

    }

    /**
     * Saves uploaded files.
     */
    protected function saveUploadedFiles()
    {
        if ($this->_uploadedFiles) {
            foreach ($this->attributes as $attribute => $storageConfig) {
                if ($this->_uploadedFiles[$attribute]) {
                    if ($this->_uploadedFiles[$attribute] instanceof UploadedFile) {
                        if ($file = $this->saveUploadedFile($this->_uploadedFiles[$attribute], $storageConfig)) {
                            $this->_files[$attribute] = $file;
                            unset($this->_uploadedFiles[$attribute]);
                            if ($storageConfig->relation) {
                                /** @var DbFile $file */
                                if ($file->save()) {
                                    $this->owner->link($attribute, $file);
                                }
                            } else {
                                $this->owner->{$attribute . '_data'} = serialize($file);
                            }
                        }
                    } elseif (is_array($this->_uploadedFiles[$attribute])) {
                        foreach ($this->_uploadedFiles[$attribute] as $index => $item) {
                            if ($file = $this->saveUploadedFile($item, $storageConfig)) {
                                $this->_files[$attribute][] = $file;
                            }
                        }
                        unset($this->_uploadedFiles[$attribute]);
                        if ($storageConfig->relation) {
                            foreach ($this->_files[$attribute] as $file) {
                                /** @var DbFile $file */
                                if ($file->save()) {
                                    $this->owner->link($attribute, $file);
                                }
                            }
                        } else {
                            $this->owner->{$attribute . '_data'} = serialize($this->_files[$attribute]);
                        }
                    }
                }
            }
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param StorageConfig $config
     * @param int $fileIndex
     * @return FileInterface|null
     */
    protected function saveUploadedFile(UploadedFile $uploadedFile, StorageConfig $config, $fileIndex = 1)
    {
        $filesystemComponent = $this->getFilesystemComponent();
        $file = $this->createFileInstance($uploadedFile, $config);
        $path = $config->resolveFilePath($uploadedFile->name, $this->owner, $fileIndex);
        if ($path = $filesystemComponent->saveFile($file, $config->filesystem, $path, true)) {
            $file->setPath($path);
            $file->setFilesystemName($config->filesystem);
            return $file;
        } else {
            return null;
        }
    }

    /**
     * @return FilesystemComponent
     * @throws \yii\base\InvalidConfigException
     */
    protected function getFilesystemComponent()
    {
        if (!$this->_filesystemComponent) {
            $this->_filesystemComponent = Yii::$app->get('filesystem');
        }

        return $this->_filesystemComponent;
    }

    protected function linkFile(FileInterface $file, StorageConfig $config)
    {

    }

    /**
     * Creates file instance.
     *
     * @param UploadedFile $file
     * @param StorageConfig $config
     * @return FileInterface
     */
    protected function createFileInstance(UploadedFile $file, StorageConfig $config)
    {
        /** @var FileInterface $fileClass */
        $fileClass = $config->relation ? DbFile::className() : File::className();

        return $fileClass::getInstanceFromUploadedFile($file);
    }

    /**
     * Creates storage config objects from config array of attributes.
     */
    protected function normalizeAttributes()
    {
        foreach ($this->attributes as $attribute => $storageConfig) {
            $this->normalizeAttribute($attribute);
        }
    }

    protected function normalizeAttribute($name)
    {
        if (!$this->attributes[$name] instanceof $this->storageConfigClass) {
            $this->attributes[$name] = new $this->storageConfigClass($this->attributes[$name]);
        }
    }

    protected function hasUploadedFiles()
    {
        return !empty($_FILES);
    }

    /**
     * @param string $name
     * @return bool
     */
    protected function hasAttribute($name)
    {
        return isset($this->attributes[$name]);
    }

    protected function setAttribute($name, $value)
    {
        if ($value instanceof UploadedFile) {
            $this->_uploadedFiles[$name] = $value;
//            if ($this->hasRelation($name)) {
//                $this->owner->populateRelation($name, $value);
//            }
        } elseif ($value instanceof FileInterface) {
            $this->_uploadedFiles[$name] = $value;
//            if ($this->hasRelation($name)) {
//                $this->owner->populateRelation($name, $value);
//            }
        }
    }

    protected function getRelation($name)
    {
        if ($this->hasAttribute($name)) {
            $this->normalizeAttribute($name);
            return $this->attributes[$name]->relation;
        } else {
            return null;
        }
    }
}