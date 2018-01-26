<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model common\models\Category */
/* @var $form yii\widgets\ActiveForm */

extract($params);
$image = $model->id . $model->mainImage->ext;

?>

<div class="category-form">

  <?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

    <?= $form->field($model, 'parent_id')->dropDownList($parentsList) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?php 
    // $form->field($model, 'alias')->textInput(['maxlength' => true]) 
    ?>

    <?= $form->field($model, 'is_main')->checkbox(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 6]) ?>

    <?= $form->field($model, 'imageFiles[]')->fileInput(['multiple' => true, 'accept' => 'image/*']) ?>

    <?= $form->field($model, 'main_image_id')->dropDownList($dropDownList) ?>

    <?php if ( ! $model->isNewRecord && file_exists(Yii::getAlias('@gallery') . '/categories/' . $image)): ?>

      <div>
        <?= Html::img(Reasanik::$galleryPath . 'categories/' . $image,
            [
                'alt' => $model->title,
            ]
        ); ?>
      </div>

    <?php endif; ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? 'Создать' : 'Обновить', ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

  <?php ActiveForm::end(); ?>

</div>
