<?php
/**
 * subscribeit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\subscribeit\records;

use fruitstudios\subscribeit\Subscribeit;

use Craft;
use craft\db\ActiveRecord;

/**
 * @author    Fruit Studios
 * @package   Subscribeit
 * @since     1.0.0
 */
class Subscription extends ActiveRecord
{
    // Public Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%subscribeit_subscriptions}}';
    }
}
