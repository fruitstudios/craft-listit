<?php
namespace fruitstudios\listit\models;

use fruitstudios\listit\Listit;

use Craft;
use craft\base\Model;

class Subscription extends Model
{
    // Public Properties
    // =========================================================================

    public $id;
    public $userId;
    public $elementId;
    public $siteId;
    public $group = 'subscription';
    public $dateCreated;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['userId', 'elementId', 'siteId'], 'integer'],
            [['group'], 'string'],
            [['group'], 'default', 'value' => 'subscription'],
            [['userId', 'elementId', 'siteId', 'group'], 'required'],
        ];
    }
}
