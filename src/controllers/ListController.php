<?php
namespace fruitstudios\listit\controllers;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use Craft;
use craft\web\Controller;

/**
 * @author    Fruit Studios
 * @package   Listit
 * @since     1.0.0
 */
class ListitController extends Controller
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
        $this->requireLogin();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // User
        $userId = $request->getParam('userId', false);
        $user = $userId ? Craft::$app->getUsers()->getUserById($userId) : Craft::$app->getUser()->getIdentity();

        // Group
        $group = $group ?? $request->getParam('group', 'subscription');

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
        $subscription->group = $group;
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
                    'group' => $subscription->group,
                    'siteId' => $subscription->siteId
                ]
            ]);
        }

        return $this->redirectToPostedUrl($subscription);
    }

    public function actionUnSubscribe(string $group = null)
    {
        $this->requireLogin();
        $this->requirePostRequest();

        $request = Craft::$app->getRequest();

        // User
        $userId = $request->getParam('userId', false);
        $user = $userId ? Craft::$app->getUsers()->getUserById($userId) : Craft::$app->getUser()->getIdentity();

        // Group
        $group = $group ?? $request->getParam('group', 'subscription');

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
            'group' => $group,
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
