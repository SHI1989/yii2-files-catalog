<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\widgets;


use dosamigos\fileupload\FileUpload;
use eseperio\filescatalog\models\base\Inode;
use eseperio\filescatalog\models\File;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use yii\helpers\Html;
use yii\validators\FileValidator;
use yii\web\JsExpression;

/**
 * Class Uploader
 * @package eseperio\filescatalog\widgets
 */
class Uploader extends FileUpload
{

    use ModuleAwareTrait;

    /**
     * @var null
     */
    public $targetUuid = null;
    /**
     * @var string id of pjax container to be refreshed when load finished
     */
    public $pjaxId;
    /**
     * @var string jQuery selector for the container where upload errors must be displayed
     */
    public $errorsContainerSelector = "#filex-error";
    /**
     * @var string jQuery selector for the progress bar where display progress.
     */
    public $progressBarSelector = "#filex-progress";

    /**
     * @throws \yii\base\InvalidConfigException
     */
    public function init()
    {

        $this->initTargetDir();

        if (empty($this->model))
            $this->model = new File();

        $this->attribute = 'file';
        $this->url = ['/filex/default/upload'];
        $this->options['multiple'] = true;

        $fileValidator = new FileValidator();
        $this->clientOptions = [
            'maxFileSize' => $fileValidator->getSizeLimit(),
            'formData' => [
                'target' => $this->targetUuid,
            ]
        ];

        $this->registerEvents();
        parent::init();
    }

    /**
     * If target dir is not defined, save file in root
     */
    private function initTargetDir()
    {
        if (empty($this->targetUuid))
            $this->targetUuid = Inode::find()->roots()->select('uuid')->asArray()->scalar();
    }

    /**
     *
     */
    public function registerEvents()
    {

        $pjaxSnippet = '';
        if (!empty($this->pjaxId) && $this->module->usePjax) {
            $pjaxSnippet = "$.pjax.reload('#{$this->pjaxId}');";
        }

        $this->clientEvents = [
            'fileuploaddone' => new JsExpression(<<<JS
            function (e, data) {
            $.each(data.result.files, function (index, file) {
                if(file.errors){
                    $("#{$this->id}-errors").append($('<p>',file.name))
                    $.each(file.errors,(k,v)=>{
                        $("{$this->errorsContainerSelector}").append($('<p>',{
                            text: (Array.isArray(v)?v[0]:v),
                            class:'text-danger'
                        }))
                    })
                }
            });
            }
JS
            ),
            'fileuploadprogressall' => new JsExpression(<<<JS
          function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('{$this->progressBarSelector} .progress-bar').css(
            'width',
            progress + '%'
        );
        if(progress>=100){
            
            $('{$this->progressBarSelector}').hide();
                        {$pjaxSnippet}

        }
    }
JS
            ),
            'fileuploadstart' => new JsExpression(<<<JS
          function (e, data) {
        var progress = parseInt(data.loaded / data.total * 100, 10);
        $('#{$this->id}-progress').show();
    }
JS
            ),
            'fileuploadfail' => 'function(e, data) {
                                console.log(e);
                                console.log(data);
                            }',
        ];
    }

    /**
     * @inheritdoc
     */
    public function run()
    {
        $input = $this->hasModel()
            ? Html::activeFileInput($this->model, $this->attribute, $this->options)
            : Html::fileInput($this->name, $this->value, $this->options);

        echo $this->useDefaultButton
            ? $this->render($this->uploadButtonTemplateView, ['input' => $input, 'id' => $this->id])
            : $input;

        $this->registerClientScript();
    }
}
