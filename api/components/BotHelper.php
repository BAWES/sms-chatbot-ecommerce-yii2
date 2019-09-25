<?php

namespace api\components;

use Yii;
use yii\base\Exception;
use yii\db\Expression;
use yii\authclient\InvalidResponseException;
use yii\helpers\ArrayHelper;


/**
 * Helper functions to help the bot make better decisions
 */
class BotHelper
{

    /**
     * Get whether any of the words exist within array of words
     */
    public function checkStringForWords($string, $arrayOfWords)
    {
        // If $arrayOfWords is a string, convert it to array
        if(!is_array($arrayOfWords)) $arrayOfWords = [$arrayOfWords];

        // Loop and check
        foreach($arrayOfWords as $word){
            if(strpos($string, $word) !== false) {
                return true;
            }
        }

        return false;
    }


}
