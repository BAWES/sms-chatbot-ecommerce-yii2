<?php

namespace common\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "product".
 *
 * @property string $uuid
 * @property string $name_en
 * @property string $name_ar
 * @property string $marketing_text_en
 * @property string $marketing_text_ar
 * @property string $price_per_unit
 * @property string $delivery_fee
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 */
class Product extends \yii\db\ActiveRecord
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'product';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['name_en', 'name_ar', 'marketing_text_en', 'marketing_text_ar', 'price_per_unit', 'delivery_fee'], 'required'],
            [['marketing_text_en', 'marketing_text_ar'], 'string'],

            [['price_per_unit', 'delivery_fee'], 'number'],

            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_INACTIVE, self::STATUS_ACTIVE]],

            [['name_en', 'name_ar'], 'string', 'max' => 255],
        ];
    }

    /**
     * @return array
     */
    public function behaviors() {
        return [
            [
                'class' => AttributeBehavior::className(),
                'attributes' => [
                    \yii\db\ActiveRecord::EVENT_BEFORE_INSERT => 'uuid',
                ],
                'value' => function() {
                    if(!$this->uuid)
                        $this->uuid = Yii::$app->db->createCommand('SELECT uuid()')->queryScalar();

                    return $this->uuid;
                }
            ],
	        [
		        'class' => TimestampBehavior::className(),
		        'createdAtAttribute' => 'created_at',
		        'updatedAtAttribute' => 'updated_at',
		        'value' => new Expression('NOW()'),
	        ]
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'uuid' => 'Uuid',
            'name_en' => 'Name En',
            'name_ar' => 'Name Ar',
            'marketing_text_en' => 'Marketing Text',
            'marketing_text_ar' => 'Marketing Text Ar',
            'price_per_unit' => 'Price per unit',
            'delivery_fee' => 'Delivery Fee',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
