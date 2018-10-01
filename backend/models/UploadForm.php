<?php

namespace backend\models;

use Yii;
// use yii\base\Model;
// use yii\web\UploadedFile;
// use yii\behaviors\TimestampBehavior;
use yii\behaviors\SluggableBehavior;
use dastanaron\translit\Translit;
use abeautifulsite\SimpleImage;
// use yii\imagine\Image;
// use Imagine\Image\ManipulatorInterface;

/**
 * UploadForm is the model behind the upload form.
 */
class UploadForm extends \yii\db\ActiveRecord
{
  /**
   * @var UploadedFile file attribute
   */
  public $imageFile;

  protected $galleryPath = false;
  protected $imageParams;
  protected $names = ['oldUpdate' => false, 'new' => false, 'old' => false];

  protected $updateDate = true;
  private $imagePathes;

  /**
   * @return array the validation rules.
   */
  public function rules()
  {
    return [
      [['imageFile'], 'image', 'extensions' => 'gif, jpg, jpeg, png'/*, 'skipOnEmpty' => false*/],
    ];
  }

  public function behaviors()
  {
    return [
      // TimestampBehavior::className(),
      [
        'class' => SluggableBehavior::className(),
        'attribute' => 'name',
        'slugAttribute' => 'alias',
      ],
      // 'slug' => [
      //   'class' => 'Zelenin\yii\behaviors\Slug',
      //   'slugAttribute' => 'alias',
      //   'attribute' => 'name',
      //   // optional params
      //   'ensureUnique' => true,
      //   'replacement' => '-',
      //   'lowercase' => true,
      //   'immutable' => false,
      //   // If intl extension is enabled, see http://userguide.icu-project.org/transforms/general. 
      //   'transliterateOptions' => 'Russian-Latin/BGN; Any-Latin; Latin-ASCII; NFD; [:Nonspacing Mark:] Remove; NFC;'
      // ]
    ];
  }

  public function beforeSave($insert)
  {
    if (parent::beforeSave($insert))
    {
      if ($insert)
      {
        $now = time();
        $this->created_at = $now;
        $this->updated_at = $now;
      }
      else
      {
        $this->rememberOldUpdateDate();

        if ($this->updateDate)
        {
          $this->updated_at = time();
        }
      }

      return true;
    }
    else
    {
      return false;
    }
  }

  public function afterSave($insert, $changedAttributes)
  {
    parent::afterSave($insert, $changedAttributes);

    $this->updateDate = false;
  }

  public function beforeDelete()
  {
    if (parent::beforeDelete())
    {
      $this->rememberOldUpdateDate();
      return true;
    }
    else
    {
      return false;
    }
  }

  protected function getTranslitedName($name, $ext)
  {
    return (new Translit())->translit($name, true, 'ru-en') . '.' . $ext;
  }

  protected function updateImages($from, $insert)
  {
    $this->setGalleryPath();

    if (!$insert)
    {
      $this->deleteImages();
    }

    $this->createImages($from);
  }

  protected function deleteImages()
  {
    $this->setGalleryPath();

    if (/* ! $this->curModelId && */ !$this->names['old'])
    {
      return;
    }

    $this->setImagePathes(false);

    foreach ($this->imagePathes as $root => $path)
    {
      if (file_exists($path))
      {
        unlink($path);
      }
    }
  }

  protected function setGalleryPath()
  {
    if ($this->galleryPath)
    {
      return;
    }

    $this->galleryPath = Yii::getAlias('@gallery') . '/';

    foreach ($this->imageParams as $root => $params)
    {
      $this->imageParams[$root]['folder'] = $this->galleryPath . $params['folder'];
    }
  }

  private function rememberOldUpdateDate()
  {
    $this->names['oldUpdate'] = $this->getOldAttribute('updated_at');
  }

  private function createImages($from)
  {
    $this->setImagePathes();

    // Image::$driver = Image::DRIVER_GD2;

    foreach ($this->imageParams as $root => $params)
    {
      (new SimpleImage($from))->
        best_fit($params['width'], $params['height'])->
        save($this->imagePathes[$root]);

      // Image::thumbnail(
      //   $this->imageFile->tempName,
      //   $params['width'],
      //   $params['height'],
      //   ManipulatorInterface::THUMBNAIL_INSET
      // )->
      //   save($this->imagePathes[$root], ['quality' => 100]);
    }

  }

  private function setImagePathes($new = true)
  {
    $this->imagePathes = [];
    $name = $new ? $this->names['new'] : $this->names['old'];

    foreach ($this->imageParams as $root => $params)
    {
      $this->imagePathes[$root] = $params['folder']
        . $this->id . '_'
        . ($new ? $this->updated_at : $this->names['oldUpdate']) . '_'
        . ($params['prefix'] ? $params['prefix'] . '_' : '')
        . $name;
    }
  }
}
