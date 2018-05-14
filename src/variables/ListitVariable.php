<?php
namespace fruitstudios\listit\variables;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\models\Site;
use craft\db\Query;

class ListitVariable
{
    // Public Methods
    // =========================================================================

    public function isListed($targetElement, string $list = Listit::DEFAULT_LIST_HANDLE, $user = null, $site = null)
    {
        $element = $this->_getElement($targetElement);
        $user = $this->_getUser($user);
        $site = $this->_getSite($site);

        if(!$user || !$element || !$site)
        {
            return false;
        }

        $criteria = [
            'userId' => $user->id,
            'elementId' => $element->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        return Listit::$plugin->service->getSubscription($criteria);
    }

    public function getUserIds($targetElement, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $element = $this->_getElement($targetElement);
        $site = $this->_getSite($site);

        if(!$element || !$site)
        {
            return [];
        }

        $criteria = [
            'elementId' => $element->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        return Listit::$plugin->service->getSubscriptionsColumn($criteria, 'userId');
    }

    public function getUsers($targetElement, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $userIds = $this->getUserIds($targetElement, $list, $site);

        return User::find()
            ->where(['id' => $userIds])
            ->all();
    }

    public function getElementIds($user = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $user = $this->_getUser($user);
        $site = $this->_getSite($site);

        if(!$user || !$site)
        {
            return [];
        }

        $criteria = [
            'userId' => $user->id,
            'list' => $list,
            'siteId' => $site->id,
        ];

        return Listit::$plugin->service->getSubscriptionsColumn($criteria, 'elementId');
    }

    public function getElements($user = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $elementIds = $this->getElementIds($user, $list, $site);

        return User::find()
            ->where([
                'id' => $elementIds
            ])
            ->all();
    }

    // Add / Remove
    // =========================================================================

    public function add($targetElement, $user = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $element = $this->_getElement($targetElement);
        $user = $this->_getUser($user);
        $site = $this->_getSite($site);

        // Subscription
        $subscription = new Subscription();
        $subscription->userId = $user->id ?? null;
        $subscription->elementId = $element->id ?? null;
        $subscription->list = $list;
        $subscription->siteId = $site->id ?? null;

        // Save Subscription
        return Listit::$plugin->service->saveSubscription($subscription);
    }

    public function remove($targetElement, $user = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $element = $this->_getElement($targetElement);
        $user = $this->_getUser($user);
        $site = $this->_getSite($site);

        // Subscription
        $subscription = Listit::$plugin->service->getSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null
        ]);

        if (!$subscription)
        {
            return true;
        }

        return Listit::$plugin->service->deleteSubscription($subscription->id);
    }


    // Favourite
    // =========================================================================

    public function isFavorited($element, $user = null, $site = null)
    {
        return $this->isFavourited($element, $user, $site);
    }

    public function isFavourited($element, $user = null, $site = null)
    {
        return $this->isListed($element, Listit::FAVOURITE_LIST_HANDLE, $user, $site);
    }


    // Like
    // =========================================================================


    public function isLiked($element, $user = null, $site = null)
    {
        return $this->isListed($element, Listit::LIKE_LIST_HANDLE, $user, $site);
    }


    // Follow
    // =========================================================================

    public function follow($targetElement, $user = null, $site = null)
    {
        return $this->add($targetElement, $user, Listit::FOLLOW_LIST_HANDLE, $user, $site);
    }

    public function unFollow($targetElement, $user = null, $site = null)
    {
        return $this->remove($targetElement, $user, Listit::FOLLOW_LIST_HANDLE, $user, $site);
    }

    public function isFollowing($targetElement, $user = null, $site = null)
    {
        return $this->isListed($targetElement, Listit::FOLLOW_LIST_HANDLE, $user, $site);
    }

    public function isFollower($targetElement, $user = null, $site = null)
    {
        $user = $this->_getUser($user);
        $targetElement = $this->_getUser($targetElement);
        return $this->isListed($user, Listit::FOLLOW_LIST_HANDLE, $targetElement, $site);
    }

    public function isFriend($targetElement, $user = null, $site = null)
    {
        $isFollowing = $this->isFollowing($targetElement, $user, $site);
        $isFollower = $this->isFollower($targetElement, $user, $site);
        return $isFollowing && $isFollower;
    }

    public function getFollowing($user = null, $site = null)
    {
        $user = $this->_getUser($user);

        $elementIds = $this->getElementIds($user, Listit::FOLLOW_LIST_HANDLE, $site);

        return User::find()
            ->where([
                'elements.id' => $elementIds
            ])
            ->all();
    }

    public function getFollowers($element = null, $site = null)
    {
        $user = $this->_getUser($element);

        $userIds = $this->getUserIds($user, Listit::FOLLOW_LIST_HANDLE, $site);

        return User::find()
            ->where([
                'elements.id' => $userIds
            ])
            ->all();
    }

    public function getFriends($user = null, $site = null)
    {
        $user = $this->_getUser($user);

        $userIds = $this->getUserIds($user, Listit::FOLLOW_LIST_HANDLE, $site);
        $elementIds = $this->getElementIds($user, Listit::FOLLOW_LIST_HANDLE, $site);

        $friendIds = array_intersect($userIds, $elementIds);

        return User::find()
            ->where([
                'elements.id' => $friendIds
            ])
            ->all();
    }


    // Star
    // =========================================================================

    public function isStared($element, $user = null, $site = null)
    {
        return $this->isListed($element, Listit::STAR_LIST_HANDLE, $user, $site);
    }


    // Bookmark
    // =========================================================================

    public function isBookmarked($element, $user = null, $site = null)
    {
        return $this->isListed($element, Listit::BOOKMARK_LIST_HANDLE, $user, $site);
    }

    // Private Methods
    // =========================================================================

    private function _getUser($user = null)
    {
        if(!$user) {
            $user = Craft::$app->getUser()->getIdentity();
        }
        return $user instanceof User ? $user : Craft::$app->getUsers()->getUserById($userId);
    }

    private function _getElement($element = null)
    {
        return $element instanceof Element ? $element : Craft::$app->getElements()->getElementById($element);
    }

    private function _getSite($site = null)
    {
        if(!$site) {
            $site = Craft::$app->getSites()->getCurrentSite();
        }
        return $site instanceof Site ? $site : Craft::$app->getSites()->getSiteById($site);
    }
}
