<?php
namespace fruitstudios\listit\services;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\elements\User;
use craft\models\Site;
use craft\db\Query;

class Lists extends Component
{
    // Constants
    // =========================================================================

    const FOLLOW_LIST_HANDLE = 'follow';
    const STAR_LIST_HANDLE = 'star';
    const BOOKMARK_LIST_HANDLE = 'bookmark';
    const LIKE_LIST_HANDLE = 'like';
    const FAVOURITE_LIST_HANDLE = 'favourite';


    // Public Methods
    // =========================================================================

    public function isOnList(string $list, $element, $user = null, $site = null)
    {
        $element = $this->_getElement($element);

        $user = $this->_determineUser($user);
        $site = $this->_determineSite($site);

        if(!$user || !$element || !$site)
        {
            return false;
        }

        $criteria = [
            'list' => $list,
            'userId' => $user->id,
            'elementId' => $element->id,
        ];

        return Listit::$plugin->subscriptions->getSubscription($criteria);
    }

    public function getSubscriptions(string $list, $user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        $site = $this->_determineSite($site);

        if(!$user || !$site)
        {
            return [];
        }

        $criteria = [
            'elementId' => $element->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        return Listit::$plugin->subscriptions->getSubscriptions($criteria);
    }

    public function getUserIds(string $list, $element, $site = null)
    {
        $element = $this->_getElement($element);
        $site = $this->_determineSite($site);

        if(!$element || !$site)
        {
            return [];
        }

        $criteria = [
            'elementId' => $element->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        return Listit::$plugin->subscriptions->getSubscriptionsColumn($criteria, 'userId');
    }

    public function getUsers(string $list, $element, $site = null)
    {
        $userIds = $this->getUserIds($element, $list, $site);

        return User::find()
            ->where(['id' => $userIds])
            ->all();
    }

    public function getElementIds(string $list, $user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        $site = $this->_determineSite($site);

        if(!$user || !$site)
        {
            return [];
        }

        $criteria = [
            'userId' => $user->id,
            'list' => $list,
            'siteId' => $site->id,
        ];

        return Listit::$plugin->subscriptions->getSubscriptionsColumn($criteria, 'elementId');
    }

    public function getElements(string $list, $user = null, $site = null)
    {
        $elementIds = $this->getElementIds($list, $user, $site);

        return User::find()
            ->where([
                'id' => $elementIds
            ])
            ->all();
    }

    // Add / Remove
    // =========================================================================

    public function addToList(string $list, $element, $user = null, $site = null)
    {
        $element = $this->_getElement($element);
        $user = $this->_determineUser($user);
        $site = $this->_determineSite($site);

        // Create Subscription
        $subscription = Listit::$plugin->subscriptions->createSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null,
        ]);

        // Save Subscription
        return Listit::$plugin->subscriptions->saveSubscription($subscription);
    }

    public function removeFromList(string $list, $element, $user = null, $site = null)
    {
        $element = $this->_getElement($element);
        $user = $this->_determineUser($user);
        $site = $this->_determineSite($site);

        // Subscription
        $subscription = Listit::$plugin->subscriptions->getSubscription([
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'list' => $list,
            'siteId' => $site->id ?? null
        ]);

        if (!$subscription)
        {
            return true;
        }

        return Listit::$plugin->subscriptions->deleteSubscription($subscription->id);
    }


    // Favourite
    // =========================================================================

    public function favourite($element, $user = null, $site = null)
    {
        return $this->addToList(self::FAVOURITE_LIST_HANDLE, $element, $user, $site);
    }

    public function unFavourite($element, $user = null, $site = null)
    {
        return $this->removeFromList(self::FAVOURITE_LIST_HANDLE, $element, $user, $site);
    }

    public function isFavourited($element, $user = null, $site = null)
    {
        return $this->isOnList(self::FAVOURITE_LIST_HANDLE, $element, $user, $site);
    }

    public function getFavourites($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getSubscriptions(self::FAVOURITE_LIST_HANDLE, $user, $site);
    }

    public function getFavouritedElements($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getElements(self::FAVOURITE_LIST_HANDLE, $user, $site);
    }


    // Favorite (US Spelling)
    // =========================================================================

    public function favorite($element, $user = null, $site = null)
    {
        return $this->favourite($element, $user, $site);
    }

    public function unFavorite($element, $user = null, $site = null)
    {
        return $this->unFavourite($element, $user, $site);
    }

    public function isFavorited($element, $user = null, $site = null)
    {
        return $this->isFavourited($element, $user, $site);
    }

    public function getFavorites($user = null, $site = null)
    {
        return $this->getFavourites($user, $site);
    }

    public function getFavoritedElements($user = null, $site = null)
    {
        return $this->getFavouritedElements($user, $site);
    }


    // Like
    // =========================================================================

    public function like($element, $user = null, $site = null)
    {
        return $this->addToList(self::LIKE_LIST_HANDLE, $element, $user, $site);
    }

    public function unLike($element, $user = null, $site = null)
    {
        return $this->removeFromList(self::LIKE_LIST_HANDLE, $element, $user, $site);
    }

    public function isLiked($element, $user = null, $site = null)
    {
        return $this->isOnList(self::LIKE_LIST_HANDLE, $element, $user, $site);
    }

    public function getLikes($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getSubscriptions(self::LIKE_LIST_HANDLE, $user, $site);
    }

    public function getLikedElements($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getElements(self::LIKE_LIST_HANDLE, $user, $site);
    }


    // Follow
    // =========================================================================

    public function follow($element, $user = null, $site = null)
    {
        return $this->addToList(self::FOLLOW_LIST_HANDLE, $element, $user, $site);
    }

    public function unFollow($element, $user = null, $site = null)
    {
        return $this->removeFromList(self::FOLLOW_LIST_HANDLE, $element, $user, $site);
    }

    public function isFollowing($element, $user = null, $site = null)
    {
        return $this->isOnList(self::FOLLOW_LIST_HANDLE, $element, $user, $site);
    }

    public function isFollower($element, $user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        $element = $this->_determineUser($element);
        return $this->isOnList(self::FOLLOW_LIST_HANDLE, $user, $element, $site);
    }

    public function isFriend($element, $user = null, $site = null)
    {
        $isFollowing = $this->isFollowing($element, $user, $site);
        $isFollower = $this->isFollower($element, $user, $site);
        return $isFollowing && $isFollower;
    }

    public function getFollowing($user = null, $site = null)
    {
        $user = $this->_determineUser($user);

        $elementIds = $this->getElementIds(self::FOLLOW_LIST_HANDLE, $user, $site);

        return User::find()
            ->where([
                'elements.id' => $elementIds
            ])
            ->all();
    }

    public function getFollowers($element = null, $site = null)
    {
        $user = $this->_determineUser($element);

        $userIds = $this->getUserIds(self::FOLLOW_LIST_HANDLE, $user, $site);

        return User::find()
            ->where([
                'elements.id' => $userIds
            ])
            ->all();
    }

    public function getFriends($user = null, $site = null)
    {
        $user = $this->_determineUser($user);

        $userIds = $this->getUserIds(self::FOLLOW_LIST_HANDLE, $user, $site);
        $elementIds = $this->getElementIds(self::FOLLOW_LIST_HANDLE, $user, $site);

        return User::find()
            ->where([
                'elements.id' => array_intersect($userIds, $elementIds)
            ])
            ->all();
    }


    // Star
    // =========================================================================

    public function star($element, $user = null, $site = null)
    {
        return $this->addToList(self::STAR_LIST_HANDLE, $element, $user, $site);
    }

    public function unStar($element, $user = null, $site = null)
    {
        return $this->removeFromList(self::STAR_LIST_HANDLE, $element, $user, $site);
    }

    public function isStared($element, $user = null, $site = null)
    {
        return $this->isOnList(self::STAR_LIST_HANDLE, $element, $user, $site);
    }

    public function getStarredElements($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getElements(self::STAR_LIST_HANDLE, $user, $site);
    }


    // Bookmark
    // =========================================================================

    public function bookmark($element, $user = null, $site = null)
    {
        return $this->addToList(self::BOOKMARK_LIST_HANDLE, $element, $user, $site);
    }

    public function unBookmark($element, $user = null, $site = null)
    {
        return $this->removeFromList(self::BOOKMARK_LIST_HANDLE, $element, $user, $site);
    }

    public function isBookmarked($element, $user = null, $site = null)
    {
        return $this->isOnList(self::BOOKMARK_LIST_HANDLE, $element, $user, $site);
    }

    public function getBookmarkedElements($user = null, $site = null)
    {
        $user = $this->_determineUser($user);
        return $this->getElements(self::BOOKMARK_LIST_HANDLE, $user, $site);
    }

    // Private Methods
    // =========================================================================

    private function _determineUser($user = null)
    {
        $user = !$user ? Craft::$app->getUser()->getIdentity() : $user;
        if($user instanceof User)
        {
            return $user;
        }
        return $user ? Craft::$app->getUsers()->getUserById((int) $user) : false;
    }

    private function _getElement($element = null)
    {
        if($element instanceof Element)
        {
            return $element;
        }
        return $element ? Craft::$app->getElements()->getUsgetElementByIderById((int) $element) : false;
    }

    private function _determineSite($site = null)
    {
        $site = !$site ? Craft::$app->getSites()->getCurrentSite() : $site;
        if($site instanceof Site)
        {
            return $site;
        }

        return $site ? Craft::$app->getSites()->getSiteById((int) $site) : false;
    }
}
