<?php
namespace common\models;

use Yii;
use yii\base\NotSupportedException;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveRecord;
use yii\web\IdentityInterface;
use yii\db\Expression;

/**
 * User model
 *
 * @property string $phone
 * @property string $name
 * @property string $email
 * @property string $auth_key
 * @property integer $status
 * @property integer $language_preferred
 * @property integer $created_at
 * @property integer $updated_at
 */
class User extends ActiveRecord implements IdentityInterface
{
    const STATUS_DELETED = 0;
    const STATUS_INACTIVE = 9;
    const STATUS_ACTIVE = 10;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%user}}';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
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
    public function rules()
    {
        return [
            ['status', 'default', 'value' => self::STATUS_INACTIVE],
            ['status', 'in', 'range' => [self::STATUS_ACTIVE, self::STATUS_INACTIVE, self::STATUS_DELETED]],
        ];
    }

    public function getStatusText(){
        switch($this->status){
            case User::STATUS_DELETED:
                return "Deleted";
                break;
            case User::STATUS_INACTIVE:
                return "Inactive";
                break;
            case User::STATUS_ACTIVE:
                return "Active";
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'phone' => 'Phone',
            'name' => 'Name',
            'email' => 'Email',
            'auth_key' => 'Key',
            'status' => 'Status',
            'language_preferred' => 'Language Preferred',
            'created_at' => 'Created At',
            'updated_at' => 'Last Activity',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentity($phone)
    {
        return static::findOne(['phone' => $phone, 'status' => self::STATUS_ACTIVE]);
    }

    /**
     * {@inheritdoc}
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        throw new NotSupportedException('"findIdentityByAccessToken" is not implemented.');
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return $this->getPrimaryKey();
    }

    /**
     * {@inheritdoc}
     */
    public function getAuthKey()
    {
        return $this->auth_key;
    }

    /**
     * {@inheritdoc}
     */
    public function validateAuthKey($authKey)
    {
        return $this->getAuthKey() === $authKey;
    }

    /**
     * [sendMessage description]
     * @param  string $message [description]
     */
    public function sendMessageFromBot($message){
        $sms = new Sms();
        $sms->user_phone = $this->phone;
        $sms->status = Sms::STATUS_UNSENT;
        $sms->sender = Sms::SENDER_BOT;
        $sms->body = $message;
        $sms->save();
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getSms()
    {
        return $this->hasMany(Sms::className(), ['user_phone' => 'phone']);
    }

}
