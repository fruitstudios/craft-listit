<?php
/**
 * subscribeit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\subscribeit\models;

use fruitstudios\subscribeit\Subscribeit;

use Craft;
use craft\base\Model;

/**
 * @author    Fruit Studios
 * @package   Subscribeit
 * @since     1.0.0
 */
class Subscription extends Model
{
    // Public Properties
    // =========================================================================

    public $userId;
    public $elementId;
    public $group = 'subscription';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['userId', 'elementId'], 'integer'],
            [['group'], 'string'],
            ['group', 'default', 'value' => 'subscription'],
            [['userId', 'elementId', 'group'], 'required'],
        ];
    }
}
