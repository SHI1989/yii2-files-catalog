<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\models;

use eseperio\admintheme\helpers\Html;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\UserException;
use yii\helpers\FileHelper;
use yii\helpers\Inflector;
use yii\web\UploadedFile;

/**
 * Class File
 * @package eseperio\filescatalog\models
 * @property FileVersion[] $versions
 */
class File extends Inode
{
    use ModuleAwareTrait;

    /**
     * @var UploadedFile
     */
    public $file;
    /**
     * @var bool whether file instance is a version
     */
    private $inodeType = InodeTypes::TYPE_FILE;

    /**
     * @return array
     */
    public function rules()
    {
        return array_replace_recursive(parent::rules(), [
            ['file', 'file', 'skipOnEmpty' => false]
        ]);
    }

    public function beforeSave($insert)
    {
        if (!empty($this->uuid) && $insert) {
            $id = File::find()->where(['uuid' => $this->uuid])->select('id')->scalar();
            if (empty($id))
                throw new UserException(Yii::t('filescatalog', 'File not found'));
            $this->inodeType = InodeTypes::TYPE_VERSION;
        }

        return parent::beforeSave($insert);
    }

    /**
     * @return int
     */
    public function getInodeType()
    {
        return $this->inodeType;
    }

    /**
     * @param bool $insert
     * @param array $changedAttributes
     * @return bool|void
     * @throws \Throwable
     * @throws \yii\base\UserException
     * @throws \yii\db\StaleObjectException
     */
    public function afterSave($insert, $changedAttributes)
    {
        if ($insert) {
            try {
                $uploadedFile = $this->file;
                if ($uploadedFile instanceof UploadedFile && $this->validate(['file'])) {
                    $this->name = Inflector::slug($uploadedFile->baseName);
                    $this->mime = FileHelper::getMimeType($uploadedFile->tempName);
                    $this->extension = mb_strtolower(Html::encode($uploadedFile->extension));
                    $this->filesize = $uploadedFile->size;
                    $filesystem = $this->module->getStorageComponent();
                    $tmpFile = fopen($uploadedFile->tempName, 'r+');
                    $inodeRealPath = $this->getInodeRealPath();
                    if ($this->module->checkFilesIntegrity)
                        $this->md5hash = hash_file('md5', $uploadedFile->tempName);

                    $this->update(false);
                    $this->validate();

                    $method = "writeStream";

                    if ($this->module->allowOverwrite && $filesystem->has($inodeRealPath))
                        $method = "updateStream";


                    if ($filesystem->{$method}($inodeRealPath, $tmpFile)) {
                        return;
                    } else {
                        $this->addError(Yii::t('filescatalog', 'Unable to move file to its destination'));
                    }

                } else {
                    $this->delete();
                }
            } catch (\Throwable $e) {
                $this->addError('file', Yii::t('filescatalog', $e->getMessage()));
                $this->delete();
            }

            if ($this->inodeType === InodeTypes::TYPE_VERSION && $insert) {
                $version = new FileVersion();
                $version->file_id = $this->id;
                $version->save();
            }

        }

        parent::afterSave($insert, $changedAttributes);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVersions()
    {
        return $this->hasMany(FileVersion::class, ['file_id' => 'id']);
    }
}
