<?php
/**
 * listit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\listit\migrations;

use fruitstudios\listit\Listit;

use Craft;
use craft\config\DbConfig;
use craft\db\Migration;

/**
 * @author    Fruit Studios
 * @package   Listit
 * @since     1.0.0
 */
class Install extends Migration
{
    // Public Properties
    // =========================================================================

    public $driver;

    // Public Methods
    // =========================================================================

    public function safeUp()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        if ($this->createTables())
        {
            $this->createIndexes();
            $this->addForeignKeys();
            Craft::$app->db->schema->refresh();
        }

        return true;
    }

    public function safeDown()
    {
        $this->driver = Craft::$app->getConfig()->getDb()->driver;

        $this->removeTables();

        return true;
    }

    // Protected Methods
    // =========================================================================

    protected function createTables()
    {
        $tablesCreated = false;

        $tableSchema = Craft::$app->db->schema->getTableSchema('{{%listit_subscriptions}}');
        if($tableSchema === null)
        {
            $tablesCreated = true;
            $this->createTable(
                '{{%listit_subscriptions}}',
                [
                    'id' => $this->primaryKey(),
                    'userId' => $this->integer()->notNull(),
                    'elementId' => $this->integer()->notNull(),
                    'list' => $this->string(64)->notNull()->defaultValue(''),
                    'siteId' => $this->integer()->notNull(),
                    'dateCreated' => $this->dateTime()->notNull(),
                    'dateUpdated' => $this->dateTime()->notNull(),
                    'uid' => $this->uid(),
                ]
            );
        }

        return $tablesCreated;
    }

    protected function createIndexes()
    {
        $this->createIndex(
            $this->db->getIndexName('{{%listit_subscriptions}}', 'list', true),
            '{{%listit_subscriptions}}',
            'list',
            true
        );

        // Additional commands depending on the db driver
        switch ($this->driver) {
            case DbConfig::DRIVER_MYSQL:
                break;
            case DbConfig::DRIVER_PGSQL:
                break;
        }
    }

    protected function addForeignKeys()
    {
        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listit_subscriptions}}', 'userId'),
            '{{%listit_subscriptions}}',
            'userId',
            '{{%users}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listit_subscriptions}}', 'elementId'),
            '{{%listit_subscriptions}}',
            'elementId',
            '{{%elements}}',
            'id',
            'CASCADE',
            'CASCADE'
        );

        $this->addForeignKey(
            $this->db->getForeignKeyName('{{%listit_subscriptions}}', 'siteId'),
            '{{%listit_subscriptions}}',
            'siteId',
            '{{%sites}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    protected function removeTables()
    {
        $this->dropTableIfExists('{{%listit_subscriptions}}');
    }
}
