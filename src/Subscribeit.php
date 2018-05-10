<?php
/**
 * subscribeit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\subscribeit;

use fruitstudios\subscribeit\services\SubscribeitService;
use fruitstudios\subscribeit\variables\SubscribeitVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class Subscribeit
 *
 * @author    Fruit Studios
 * @package   Subscribeit
 * @since     1.0.0
 *
 * @property  SubscribeitServiceService $subscribeitService
 */
class Subscribeit extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var Subscribeit
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('subscribeit', SubscribeitVariable::class);
            }
        );


        Craft::info(
            Craft::t(
                'subscribeit',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

    // Protected Methods
    // =========================================================================

}
