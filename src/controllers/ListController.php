<?php
namespace fruitstudios\listit\controllers;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

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

        $request = Craft::$app->getRequest();

        $user = $this->_getUser();
        $element = $this->_getElement();
        $list = $this->_getList();
        $site = $this->_getsite();

        // Subscription
        $subscription = new Subscription();
        $subscription->userId = $user->id ?? null;
        $subscription->elementId = $element->id ?? null;
        $subscription->list = $list;
        $subscription->siteId = $site->id ?? null;

        // Save Subscription
        if (!Listit::$plugin->service->saveSubscription($subscription))
        {
            if ($request->getAcceptsJson())
            {
                return $this->asJson([
                    'success' => false,
                    'errors' => $subscription->getErrors(),
                ]);
            }

            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $subscription
            ]);

            return null;
        }

        if ($request->getAcceptsJson()) {
            return $this->asJson([
                'success' => true,
                'subscription' => [
                    'id' => $subscription->id,
                    'userId' => $subscription->userId,
                    'elementId' => $subscription->elementId,
                    'list' => $subscription->list,
                    'siteId' => $subscription->siteId
                ]
            ]);
        }

        return $this->redirectToPostedUrl($subscription);
    }

    public function actionRemove()
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        $user = $this->_getUser();
        $element = $this->_getElement();
        $list = $this->_getList();
        $site = $this->_getsite();

        // Subscription
        $subscription = Listit::$plugin->service->getSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null
        ]);

        // Subscription exists?
        if ($subscription)
        {
            if (!Listit::$plugin->service->deleteSubscription($subscription->id))
            {
                $result = [
                    'success' => false,
                    'error' => 'Could not delete subscription',p
                ];

                if ($request->getAcceptsJson())
                {
                    return $this->asJson($result);
                }

                Craft::$app->getUrlManager()->setRouteParams([
                    'subscription' => $result
                ]);

                return null;
            }

            if ($request->getAcceptsJson()) {
                return $this->asJson([
                    'success' => true,
                ]);
            }

            return $this->redirectToPostedUrl();
        }
        else
        {
            $result = [
                'success' => true,
                'message' => 'Subscription does not exist'
            ];

            if ($request->getAcceptsJson())
            {
                return $this->asJson($result);
            }

            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $result
            ]);

            return null;
        }
    }


    // Follow
    // =========================================================================

    public function actionFollow()
    {
        $this->_list = Listit::FOLLOW_LIST_HANDLE;
        $this->_requireElementOfType(User::class);
        return $this->actionAdd();
    }

    public function actionUnFollow()
    {
        $this->_list = Listit::FOLLOW_LIST_HANDLE;
        $this->_requireElementOfType(User::class);
        return $this->actionRemove();
    }


    // Favourite
    // =========================================================================

    public function actionFavorite()
    {
        return $this->actionFavourite();
    }

    public function actionFavourite()
    {
        $this->_list = Listit::FAVOURITE_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnFavorite()
    {
        return $this->actionUnFavourite();
    }

    public function actionUnFavourite()
    {
        $this->_list = Listit::FAVOURITE_LIST_HANDLE;
        return $this->actionRemove();
    }


    // Like
    // =========================================================================

    public function actionLike()
    {
        $this->_list = Listit::LIKE_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnLike()
    {
        $this->_list = Listit::LIKE_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Star
    // =========================================================================

    public function actionStar()
    {
        $this->_list = Listit::STAR_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnStar()
    {
        $this->_list = Listit::STAR_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Bookmark
    // =========================================================================

    public function actionBookmark()
    {
        $this->_list = Listit::BOOKMARK_LIST_HANDLE;
        return $this->actionAdd();
    }

    public function actionUnBookmark()
    {
        $this->_list = Listit::BOOKMARK_LIST_HANDLE;
        return $this->actionRemove();
    }

    // Private
    // =========================================================================

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

    private function _getList()
    {
        if($this->_list)
        {
            return $this->_list;
        }

        return $list ?? Craft::$app->getRequest()->getParam('list', Listit::DEFAULT_LIST_HANDLE);
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
            $result = [
                'success' => false,
                'error' => 'Element must be of type:  '.$type,
            ];

            if ($request->getAcceptsJson())
            {
                return $this->asJson($result);
            }

            Craft::$app->getUrlManager()->setRouteParams([
                'subscription' => $result
            ]);

            return null;
        }

    }

}
