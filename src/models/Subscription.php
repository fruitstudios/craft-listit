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
    public $list = Listit::DEFAULT_LIST_HANDLE;
    public $dateCreated;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['userId', 'elementId', 'siteId'], 'integer'],
            [['list'], 'string'],
            [['list'], 'default', 'value' => Listit::DEFAULT_LIST_HANDLE],
            [['userId', 'elementId', 'siteId', 'list'], 'required'],
        ];
    }
}
