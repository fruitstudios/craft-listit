<?php
namespace fruitstudios\listit\controllers;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use Craft;
use craft\web\Controller;

class ListController extends Controller
{

    // Protected Properties
    // =========================================================================

    protected $allowAnonymous = [];


    // Public Methods
    // =========================================================================

    public function actionIndex()
    {
        return $this->actionAdd();
    }

    public function actionAdd(string $list = null)
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // User
        $userId = $request->getParam('userId', false);
        $user = $userId ? Craft::$app->getUsers()->getUserById($userId) : Craft::$app->getUser()->getIdentity();

        // Group
        $list = $list ?? $request->getParam('list', Listit::DEFAULT_LIST_HANDLE);

        // Site
        $siteId = $request->getParam('siteId', false);
        $site = $siteId ? Craft::$app->getSites()->getSiteById($siteId) : Craft::$app->getSites()->getCurrentSite();

        // Element
        $elementId = $request->getParam('elementId', false);
        $element = $elementId ? Craft::$app->getElements()->getElementById($elementId) : null;

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

    public function actionRemove(string $list = null)
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // User
        $userId = $request->getParam('userId', false);
        $user = $userId ? Craft::$app->getUsers()->getUserById($userId) : Craft::$app->getUser()->getIdentity();

        // Group
        $list = $list ?? $request->getParam('list', Listit::DEFAULT_LIST_HANDLE);

        // Site
        $siteId = $request->getParam('siteId', false);
        $site = $siteId ? Craft::$app->getSites()->getSiteById($siteId) : Craft::$app->getSites()->getCurrentSite();

        // Element
        $elementId = $request->getParam('elementId', false);
        $element = $elementId ? Craft::$app->getElements()->getElementById($elementId) : null;

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
                    'error' => 'Could not delete subscription',
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

    public function actionFavorite()
    {
        return $this->actionAdd('favorite');
    }

    public function actionUnFavorite()
    {
        return $this->actionRemove('favorite');
    }

    public function actionLike()
    {
        return $this->actionAdd('like');
    }

    public function actionUnLike()
    {
        return $this->actionRemove('like');
    }

    public function actionFollow()
    {
        return $this->actionAdd('follow');
    }

    public function actionUnFollow()
    {
        return $this->actionRemove('follow');
    }

    public function actionStar()
    {
        return $this->actionAdd('star');
    }

    public function actionUnStar()
    {
        return $this->actionRemove('star');
    }

    public function actionBookmark()
    {
        return $this->actionAdd('bookmark');
    }

    public function actionUnBookmark()
    {
        return $this->actionRemove('bookmark');
    }
}
