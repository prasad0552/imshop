<?php

namespace im\filesystem\components;

use im\base\interfaces\ModelBehaviorInterface;
use im\filesystem\models\DbFile;
use yii\base\Behavior;
use yii\base\UnknownMethodException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\validators\Validator;
use yii\web\UploadedFile;
use Yii;

/**
 * Class FilesBehavior adds possibility to handle file uploads and link them to the model.
 *
 * @property ActiveRecord $owner
 * @package im\filesystem\components
 */
class FilesBehavior extends Behavior implements ModelBehaviorInterface
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
     * @var UploadedFile[]
     */
    private $_relatedFiles = [];

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
     * Handles before save event of owner.
     * Deletes old related files, creates file instances for uploaded files and sets them to owner's attributes before saving.
     */
    public function beforeSave()
    {
//        if ($this->hasUploadedFiles()) {
//            $this->deleteRelatedFiles();
//            $this->getFileInstances();
//        }
    }

    /**
     * Handles after update event of owner.
     * Saves uploaded files to filesystem.
     */
    public function afterSave()
    {
        $this->saveUploadedFiles();
        $this->linkRelatedFiles();
    }

    /**
     * Handles after update event of owner.
     * Saves uploaded files to filesystem.
     */
    public function afterUpdate()
    {

    }

    /**
     * Handles after insert event of owner.
     * Updates related files for in case where file name contains owner primary key.
     */
    public function afterInsert()
    {

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
        try {
            parent::__set($name, $value);
        } catch (\Exception $e) {
            $this->_uploadedFiles[$name] = $value;
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
        try {
            return parent::__get($name);
        } catch (\Exception $e) {
            return isset($this->_uploadedFiles[$name]) ? $this->_uploadedFiles[$name] : null;
        }
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
                    $relation = $this->owner->getRelation($storageConfig->relation);
                    /** @var FileInterface $modelClass */
                    $modelClass = $relation->modelClass;
                    if ($this->_uploadedFiles[$attribute] instanceof UploadedFile) {
                        $model = $modelClass::getInstanceFromUploadedFile($this->_uploadedFiles[$attribute]);
                        if ($model = $this->saveUploadedFile($this->_uploadedFiles[$attribute], $model, $storageConfig)) {
                            $this->_relatedFiles[$attribute]['models'][] = $model;
                        }
                    } elseif (is_array($this->_uploadedFiles[$attribute])) {
                        foreach ($this->_uploadedFiles[$attribute] as $index => $file) {
                            $model = $modelClass::getInstanceFromUploadedFile($file);
                            if ($model = $this->saveUploadedFile($file, $model, $storageConfig, $index + 1)) {
                                $this->_relatedFiles[$attribute]['models'][] = $model;
                            }
                        }
                    }
                    unset($this->_uploadedFiles[$attribute]);
                }
            }
        }
    }

    protected function linkRelatedFiles()
    {
        if ($this->_relatedFiles) {
            foreach ($this->attributes as $attribute => $storageConfig) {
                if (isset($this->_relatedFiles[$attribute])) {
                    $this->owner->{$storageConfig->relation} = $this->_relatedFiles[$attribute]['models'];
                    unset($this->_relatedFiles[$attribute]);
                }
            }
            $this->owner->save(false);
        }
    }

    /**
     * @param UploadedFile $uploadedFile
     * @param FileInterface $file
     * @param StorageConfig $config
     * @param int $fileIndex
     * @return FileInterface|null
     */
    protected function saveUploadedFile(UploadedFile $uploadedFile, FileInterface $file, StorageConfig $config, $fileIndex = 1)
    {
        $filesystemComponent = $this->getFilesystemComponent();
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

    /**
     * @inheritdoc
     */
    public function load($data)
    {
        $this->normalizeAttributes();
        foreach ($this->attributes as $attribute => $storageConfig) {
            if (isset($data[$attribute])) {
                /** @var ActiveRecord $modelClass */
                $modelClass = $storageConfig->fileClass;
                $pks = array_keys($data[$attribute]);
                $models = $modelClass::find()->where(['id' => $pks])->indexBy('id')->all();
                foreach ($models as $i => $model) {
                    if (!empty($data[$attribute][$i])) {
                        $model->load($data[$attribute][$i], '');
                        $this->_relatedFiles[$attribute]['models'][$i] = $model;
                        $extraColumns = array_diff(array_keys($data[$attribute][$i]), $model->safeAttributes());
                        if ($extraColumns) {
                            $this->_relatedFiles[$attribute]['extraColumns'][$i] = array_intersect_key($data[$attribute][$i], array_flip($extraColumns));
                        }
                    }
                }
//                if ($modelClass::loadMultiple($models, $data[$attribute], '')) {
//                    $this->owner->populateRelation($attribute, $models);
//                    $this->_relatedFiles[$attribute]['models'] = $models;
//                    $this->_relatedFiles[$attribute]['extraColumns'] = $models;
//                    $extraColumns = array_diff(reset($data[$attribute]), )
//                }
            }
        }

        return true;
    }

    protected function getFileItemsToUpdate()
    {

    }
}