<?php
/**
 * Copyright (c) 2019. Grupo Smart (Spain)
 *
 * This software is protected under Spanish law. Any distribution of this software
 * will be prosecuted.
 *
 * Developed by Waizabú <code@waizabu.com>
 * Updated by: erosdelalamo on 18/7/2019
 *
 *
 */


/* @var $parent \eseperio\filescatalog\models\Directory */

use yii\bootstrap\ActiveForm;
use yii\bootstrap\Html;

?>


<div class="directory-form">
    <div class="row">
        <div class="col-md-4 col-md-offset-4">
            <div class="panel">
                <div class="panel-header">

                    <h3><span class="fiv-icon-folder fiv-sqo"></span> <?= Html::encode($parent->name) ?>/...</h3>
                </div>
                <div class="panel-body">
                    <?php $form = ActiveForm::begin(); ?>

                    <?= $form->field($model, 'name')->textInput(['maxlength' => 256]) ?>

                    <div class="form-group">
                        <?= Html::submitButton('Add', ['class' => 'btn btn-success']) ?>
                    </div>

                    <?php ActiveForm::end(); ?>
                </div>
            </div>
        </div>
    </div>


</div>
