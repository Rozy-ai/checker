<?php

namespace backend\models;

use Yii;

/**
 * This is the model class for table "upload_fields".
 *
 * @property int $id
 * @property string $name
 * @property string|null $comment
 * @property string|null $price_field
 * @property string|null $product_field
 * @property string $row_id
 * @property int $position
 * @property int $default_visible
 * @property int $is_select_field
 */
class UploadFields extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'upload_fields';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name', 'row_id', 'position', 'default_visible'], 'required'],
            [['position', 'default_visible', 'is_select_field'], 'integer'],
            [['name'], 'string', 'max' => 512],
            [['comment'], 'string', 'max' => 1024],
            [['price_field'], 'string', 'max' => 2048],
            [['product_field'], 'string', 'max' => 256],
            [['row_id'], 'string', 'max' => 255],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'name' => 'Name',
            'comment' => 'Comment',
            'price_field' => 'Price Field',
            'product_field' => 'Product Field',
            'row_id' => 'Row ID',
            'position' => 'Position',
            'default_visible' => 'Default Visible',
            'is_select_field' => 'Is Select Field',
        ];
    }
}
