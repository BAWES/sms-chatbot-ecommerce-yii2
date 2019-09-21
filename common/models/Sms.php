<?php

namespace common\models;

use Yii;
use yii\behaviors\AttributeBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "sms".
 *
 * @property string $uuid
 * @property string $user_phone
 * @property int $sender
 * @property string $body
 * @property int $status
 * @property string $created_at
 * @property string $updated_at
 *
 * @property User $userPhone
 */
class Sms extends \yii\db\ActiveRecord
{
    const STATUS_UNSENT = 0;
    const STATUS_SENT = 1;

    const SENDER_USER = 0;
    const SENDER_BOT = 9;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'sms';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sender', 'body'], 'required'],

            ['sender', 'in', 'range' => [self::SENDER_USER, self::SENDER_BOT]],

            ['status', 'default', 'value' => self::STATUS_SENT],
            ['status', 'in', 'range' => [self::STATUS_UNSENT, self::STATUS_SENT]],

            [['user_phone'], 'string', 'max' => 255],
            [['user_phone'], 'unique'],
            [['user_phone'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['user_phone' => 'phone']],
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
                        $this->uuid = 'sms_'.Yii::$app->db->createCommand('SELECT uuid()')->queryScalar();

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
            'user_phone' => 'User Phone',
            'sender' => 'Sender',
            'body' => 'Body',
            'status' => 'Status',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::className(), ['phone' => 'user_phone']);
    }
}
