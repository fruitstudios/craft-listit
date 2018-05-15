<?php
namespace fruitstudios\listit\controllers;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use fruitstudios\listit\services\Lists;

use Craft;
use craft\web\Controller;
use craft\elements\User;

class ListController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = [];

    // Private
    // =========================================================================

    private $_list;
    private $_user;
    private $_element;
    private $_site;

    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        return $this->actionAdd();
    }

    public function actionAdd()
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $user = $this->_getUser();
        $element = $this->_getElement();
        $list = $this->_getList();
        $site = $this->_getSite();

        // Create Subscription
        $subscription = Listit::$plugin->subscriptions->createSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null,
        ]);

        // Save Subscription
        if (!Listit::$plugin->subscriptions->saveSubscription($subscription))
        {
            return $this->_handleFailedResponse($subscription);
        }
        return $this->_handleSuccessfulResponse($subscription);
    }

    public function actionRemove()
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $user = $this->_getUser();
        $element = $this->_getElement();
        $list = $this->_getList();
        $site = $this->_getSite();

        // Get Subscription
        $subscription = Listit::$plugin->subscriptions->getSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null
        ]);

        // No Subscription Found
        if (!$subscription)
        {
            return $this->_handleFailedResponse(false, [
                'error' => Craft::t('listit', 'Subscription does not exist')
            ]);
        }

        // Delete Subscription
        if (!Listit::$plugin->subscriptions->deleteSubscription($subscription->id))
        {
            return $this->_handleFailedResponse($subscription, [
                'error' => Craft::t('listit', 'Could not delete subscription')
            ]);
        }
        return $this->_handleSuccessfulResponse();
    }


    // Follow
    // =========================================================================

    public function actionFollow()
    {
        $this->_list = Lists::FOLLOW_LIST_HANDLE;
        $this->_requireElementOfType(User::class);
        return $this->actionAdd();
    }

    public function actionUnFollow()
    {
        $this->_list = Lists::FOLLOW_LIST_HANDLE;
        $this->_requireElementOfType(User::class);
        return $this->actionRemove();
    }


    // Favourite
    // =========================================================================

    public function actionFavourite()
    {
        $this->_list = Lists::FAVOURITE_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnFavourite()
    {
        $this->_list = Lists::FAVOURITE_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Favorite (US Spelling)
    // =========================================================================

    public function actionFavorite()
    {
        return $this->actionFavourite();
    }

    public function actionUnFavorite()
    {
        return $this->actionUnFavourite();
    }

    // Like
    // =========================================================================

    public function actionLike()
    {
        $this->_list = Lists::LIKE_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnLike()
    {
        $this->_list = Lists::LIKE_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Star
    // =========================================================================

    public function actionStar()
    {
        $this->_list = Lists::STAR_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnStar()
    {
        $this->_list = Lists::STAR_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Bookmark
    // =========================================================================

    public function actionBookmark()
    {
        $this->_list = Lists::BOOKMARK_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnBookmark()
    {
        $this->_list = Lists::BOOKMARK_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Private
    // =========================================================================

    private function _getList()
    {
        if($this->_list)
        {
            return $this->_list;
        }

        return $list ?? Craft::$app->getRequest()->getParam('list');
    }

    private function _getUser()
    {
        if($this->_user)
        {
            return $this->_user;
        }

        $userId = Craft::$app->getRequest()->getParam('userId', false);
        return $userId ? Craft::$app->getUsers()->getUserById($userId) : Craft::$app->getUser()->getIdentity();
    }

    private function _getElement()
    {
        if($this->_element)
        {
            return $this->_element;
        }

        $elementId = Craft::$app->getRequest()->getParam('elementId', false);
        return $elementId ? Craft::$app->getElements()->getElementById($elementId) : null;
    }

    private function _getSite()
    {
        if($this->_element)
        {
            return $this->_element;
        }

        $siteId = Craft::$app->getRequest()->getParam('siteId', false);
        return $siteId ? Craft::$app->getSites()->getSiteById($siteId) : Craft::$app->getSites()->getCurrentSite();

    }

    private function _requireElementOfType($type)
    {
        $element = $this->_getElement();

        if (!$element->className() === $type)
        {
            return $this->_handleFailedResponse($subscription, [
                'error' => Craft::t('listit', 'Element must be a {type}', [
                    'type' => $type
                ])
            ]);
        }
    }

    private function _handleSuccessfulResponse($subscription = null, array $result = [])
    {
        $result['success'] = true;

        if (Craft::$app->getRequest()->getAcceptsJson())
        {
            if($subscription instanceof Subscription)
            {
                $result['subscription'] = [
                    'id' => $subscription->id,
                    'userId' => $subscription->userId,
                    'elementId' => $subscription->elementId,
                    'list' => $subscription->list,
                    'siteId' => $subscription->siteId
                ];
            }
            return $this->asJson($result);
        }

        $result['subscription'] = $subscription;
        Craft::$app->getUrlManager()->setRouteParams([
            'listit' => $result
        ]);

        return $this->redirectToPostedUrl();
    }

    private function _handleFailedResponse($subscription = null, array $result = [])
    {
        $result['success'] = false;

        if (Craft::$app->getRequest()->getAcceptsJson())
        {
            if($subscription instanceof Subscription)
            {
                $result['errors'] = $subscription->getErrors();
            }
            return $this->asJson($result);
        }

        $result['subscription'] = $subscription;
        Craft::$app->getUrlManager()->setRouteParams([
            'listit' => $result
        ]);

        return null;
    }

}
