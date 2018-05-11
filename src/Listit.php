<?php
namespace fruitstudios\listit;

use fruitstudios\listit\services\ListitService;
use fruitstudios\listit\variables\ListitVariable;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\web\UrlManager;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

class Listit extends Plugin
{

    // Constants
    // =========================================================================

    const DEFAULT_LIST_HANDLE = 'default';

    // Static Properties
    // =========================================================================

    public static $plugin;

    // Public Properties
    // =========================================================================

    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        $this->setComponents([
            'service' => ListitService::class,
        ]);

        Event::on(
            CraftVariable::class,
            CraftVariable::EVENT_INIT,
            function (Event $event) {
                $variable = $event->sender;
                $variable->set('listit', ListitVariable::class);
            }
        );


        Craft::info(
            Craft::t(
                'listit',
                '{name} plugin loaded',
                ['name' => $this->name]
            ),
            __METHOD__
        );
    }

}
