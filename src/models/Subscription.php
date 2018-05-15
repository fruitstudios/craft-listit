<?php
namespace fruitstudios\listit\models;

use fruitstudios\listit\Listit;

use Craft;
use craft\base\Model;

class Subscription extends Model
{
    // Private Properties
    // =========================================================================

    private $_user;
    private $_element;

    // Public Properties
    // =========================================================================

    public $id;
    public $userId;
    public $elementId;
    public $list;
    public $siteId;
    public $dateCreated;

    // Public Methods
    // =========================================================================

    public function rules()
    {
        return [
            [['userId', 'elementId', 'siteId'], 'integer'],
            [['list'], 'string'],
            [['userId', 'elementId', 'siteId', 'list'], 'required'],
        ];
    }

    public function getUser()
    {
        if(is_null($this->_user))
        {
            $this->_user = Craft::$app->getUsers()->getUserById($this->userId);
        }
        return $this->_user;
    }

    public function getElement()
    {
        if(is_null($this->_element))
        {
            $this->_element = Craft::$app->getElements()->getElementById($this->elementId);
        }
        return $this->_element;
    }
}
