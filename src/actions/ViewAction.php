<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\web\Controller;

class ViewAction extends Action
{
    use ModuleAwareTrait;
    /**
     * @var DefaultController|Controller|\yii\rest\Controller
     */
    public $controller;

    public function run()
    {
        $model = $this->controller->findModel(Yii::$app->request->get('uuid', false));

        /* @todo: Check ACL */

        $allowedMimes = $this->module->browserInlineMimeTypes;
        $canBeDisplayed = false;
        if (in_array($model->mime, $allowedMimes)) {
            $canBeDisplayed = true;
        }

        return $this->controller->render('view', [
            'model' => $model,
            'canBeDisplayed' => $canBeDisplayed,
            'checkFilesIntegrity' => $this->module->checkFilesIntegrity
        ]);

    }
}
