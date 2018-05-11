<?php
namespace fruitstudios\listit\variables;

use fruitstudios\listit\Listit;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\models\Site;
use craft\db\Query;

class ListitVariable
{
    // Public Methods
    // =========================================================================

    public function isListed($element, string $list = Listit::DEFAULT_LIST_HANDLE, $user = null, $site = null)
    {
        $element = $this->_getElement($element);
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

    public function getUsers($element = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $element = $this->_getElement($element);
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

        $userIds = Listit::$plugin->service->getSubscriptions($criteria, ['userId']);

        return User::find()
            ->where(['id' => $userIds])
            ->all();
    }

    public function getMutualUsers($element, string $list = Listit::DEFAULT_LIST_HANDLE, $user = null, $site = null)
    {
        // Return usrs that are both related to this element
        return;
    }

    public function getElements($user = null, string $list = Listit::DEFAULT_LIST_HANDLE, $site = null)
    {
        $user = $this->_getUser($user);
        $site = $this->_getSite($site);

        // Guard
        if(!$user || !$site)
        {
            return [];
        }

        $criteria = [
            'elementId' => $element->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        $elementIds = Listit::$plugin->service->getSubscriptions($criteria, ['elementId']);

        return Element::find()
            ->where(['id' => $userIds])
            ->all();
    }


    // Favourite
    // =========================================================================

    public function isFavourited($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'favourite', $user, $site);
    }


    // Like
    // =========================================================================


    public function isLiked($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'like', $user, $site);
    }


    // Follow
    // =========================================================================

    public function isFollowing($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'follow', $user, $site);
    }


    // Star
    // =========================================================================

    public function isStared($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'star', $user, $site);
    }


    // Bookmark
    // =========================================================================

    public function isBookmarked($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'bookmark', $user, $site);
    }

    // Private Methods
    // =========================================================================

    private function _getUser($user)
    {
        if(!$user) {
            $user = Craft::$app->getUser()->getIdentity();
        }
        return $user instanceof User ? $user : Craft::$app->getUsers()->getUserById($userId);
    }

    private function _getElement($element)
    {
        return $element instanceof Element ? $element : Craft::$app->getElements()->getElementById($element);
    }

    private function _getSite($site)
    {
        if(!$site) {
            $site = Craft::$app->getSites()->getCurrentSite();
        }
        return $site instanceof Site ? $site : Craft::$app->getSites()->getSiteById($site);
    }
}
