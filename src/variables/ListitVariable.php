<?php
namespace fruitstudios\listit\variables;

use fruitstudios\listit\Listit;

use Craft;
use craft\base\Element;
use craft\elements\User;
use craft\models\Site;

class ListitVariable
{
    // Public Methods
    // =========================================================================

    public function isSubscribed($element, string $group = 'subscription', $user = null, $site = null)
    {
        // Element
        $element = $element instanceof Element ? $element : Craft::$app->getElements()->getElementById($element);

        // User
        if(!$user) {
            $user = Craft::$app->getUser()->getIdentity();
        }
        $user = $user instanceof User ? $user : Craft::$app->getUsers()->getUserById($userId);

        // Site
        if(!$site) {
            $site = Craft::$app->getSites()->getCurrentSite();
        }
        $site = $site instanceof Site ? $site : Craft::$app->getSites()->getSiteById($site);

        // Guard
        if(!$user || !$element || !$site)
        {
            return false;
        }

        $subscribed = Listit::$plugin->service->getSubscription([
            'userId' => $user->id,
            'elementId' => $element->id,
            'siteId' => $site->id,
            'group' => $group
        ]);

        return $subscribed;
    }

    public function isFavorited($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'favorite', $user, $site);
    }

    public function isLiked($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'like', $user, $site);
    }

    public function isFollowing($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'follow', $user, $site);
    }

    public function isStared($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'star', $user, $site);
    }

    public function isBookmarked($element, $user = null, $site = null)
    {
        return $this->isSubscribed($element, 'bookmark', $user, $site);
    }
}
