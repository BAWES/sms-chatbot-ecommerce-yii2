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
 * @property string $marketing_text
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
            [['name_en', 'name_ar'], 'required'],
            [['marketing_text'], 'string'],

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
            'marketing_text' => 'Marketing Text',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }
}
