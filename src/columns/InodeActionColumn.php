<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\columns;


use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use Yii;
use yii\grid\Column;
use yii\helpers\Html;

class InodeActionColumn extends Column
{
    /**
     * @param Inode $model
     * @param mixed $key
     * @param int $index
     * @return string
     */
    public function renderDataCellContent($model, $key, $index)
    {


        $label = Yii::t('filescatalog', 'View');
        $action = 'view';
        if($model->type == InodeTypes::TYPE_DIR){
            $action = 'index';
            $label = Yii::t('filescatalog', 'Open');
        }
        $result = Html::a($label, [$action, 'uuid' => $model->uuid], ['class' => 'btn btn-default btn-sm']);

            $result .= Html::button(
                Html::tag('span', '', ['class' => 'caret']) .
                Html::tag('span', 'Toggle Dropdown', ['class' => 'sr-only']), [
                    'class' => 'btn btn-default btn-sm dropdown-toggle',
                    'data-toggle' => 'dropdown',
                    'aria-haspopup' => 'true',
                    'aria-expanded' => 'false',
                ]
            );

            $items = Html::tag(
                'li',
                Html::a(
                    Yii::t('xenon','Properties'),
                    ['properties','uuid'=>$model->uuid],
                    ['class' => 'dropdown-item'])
            );
            switch ($model->type){
                case InodeTypes::TYPE_DIR:

                    break;
            }
            $result .= Html::tag('ul', $items, ['class' => 'dropdown-menu dropdown-menu-right']);


        return Html::tag('div', $result, ['class' => 'btn-group pull-right', 'style' => 'display: flex']);
    }
}
