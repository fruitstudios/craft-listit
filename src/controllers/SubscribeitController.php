<?php
/**
 * subscribeit plugin for Craft CMS 3.x
 *
 * Follow, Favourite, Bookmark, Like & Subscribe.
 *
 * @link      https://fruitstudios.co.uk
 * @copyright Copyright (c) 2018 Fruit Studios
 */

namespace fruitstudios\subscribeit\controllers;

use fruitstudios\subscribeit\Subscribeit;

use Craft;
use craft\web\Controller;

/**
 * @author    Fruit Studios
 * @package   Subscribeit
 * @since     1.0.0
 */
class SubscribeitController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = [];


    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        return $this->actionSubscribe();
    }

    public function actionSubscribe(string $group = null)
    {
        $group = $group ?? Craft::$app->getRequest()->getParam('group', 'subscription');

        // Add subscription if not already exists

        return true;
    }

    public function actionUnSubscribe(string $group = null)
    {
        // Remove subscription if it already exists

        $group = $group ?? Craft::$app->getRequest()->getParam('group', 'subscription');

        return true;
    }

    public function actionFavorite()
    {
        return $this->actionSubscribe('favorite');
    }

    public function actionUnFavorite()
    {
        return $this->actionUnSubscribe('favorite');
    }

    public function actionLike()
    {
        return $this->actionSubscribe('like');
    }

    public function actionUnLike()
    {
        return $this->actionUnSubscribe('like');
    }

    public function actionFollow()
    {
        return $this->actionSubscribe('follow');
    }

    public function actionUnFollow()
    {
        return $this->actionUnSubscribe('follow');
    }

    public function actionStar()
    {
        return $this->actionSubscribe('star');
    }

    public function actionUnStar()
    {
        return $this->actionUnSubscribe('star');
    }

    public function actionBookmark()
    {
        return $this->actionSubscribe('bookmark');
    }

    public function actionUnBookmark()
    {
        return $this->actionUnSubscribe('bookmark');
    }
}
