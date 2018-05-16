<?php
namespace fruitstudios\listit\services;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;

use Craft;
use craft\base\Component;
use craft\base\Element;
use craft\records\Element as ElementRecord;
use craft\elements\db\ElementQueryInterface;
use craft\elements\User;
use craft\elements\Entry;
use craft\elements\Tag;
use craft\elements\Asset;
use craft\elements\Category;
use craft\elements\MatrixBlock;
use craft\models\Site;
use craft\helpers\ArrayHelper;
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

    public function isOnList($params = [])
    {
        $list = $this->_getList($params);
        $element = $this->_getElement($params);
        $user = $this->_getUser($params);
        $site = $this->_getSite($params);

        if(!$list || !$user || !$element || !$site)
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

    public function getSubscriptions($paramsOrList)
    {
        $list = $this->_getList($paramsOrList);
        $user = $this->_getUser($paramsOrList);
        $site = $this->_getSite($paramsOrList);

        if(!$list || !$user || !$site)
        {
            return [];
        }

        $criteria = [
            'userId' => $user->id,
            'siteId' => $site->id,
            'list' => $list
        ];

        return Listit::$plugin->subscriptions->getSubscriptions($criteria);
    }

    public function getOwnerIds($params)
    {
        $list = $this->_getList($params);
        $element = $this->_getElement($params);
        $site = $this->_getSite($params);

        if(!$list || !$element || !$site)
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

    public function getOwners($params)
    {
        $ownerIds = $this->getOwnerIds($params);

        return User::find()
            ->id($owenrIds)
            ->all();
    }

    public function getElementIds($params)
    {
        $list = $this->_getList($params);
        $user = $this->_getUser($params);
        $site = $this->_getSite($params);

        if(!$list || !$user || !$site)
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

    public function getElements($params)
    {
        $elementIds = $this->getElementIds($params);
        if(!$elementIds)
        {
            return [];
        }

        // Get craft element rows
        $type = $params['type'] ?? false;
        if($type)
        {
            $elements = (new Query())
                ->select(['id', 'type'])
                ->from([ElementRecord::tableName()])
                ->where([
                    'id' => $elementIds,
                    'type' => $type
                ])
                ->all();

            $criteria = $params['criteria'] ?? [];
            $criteria['id'] = $ids;

            return $this->_getElementQuery($type, $criteria)
                ->all();
        }
        else
        {
            // TODO: Is this over kill, is it even needed???
            $elementsToReturn = $elementIds;

            $elements = (new Query())
                ->select(['id', 'type'])
                ->from([ElementRecord::tableName()])
                ->where([
                    'id' => $elementIds,
                ])
                ->all();

            $elementIdsByType = [];
            foreach ($elements as $element)
            {
                $elementIdsByType[$element['type']][] = $element['id'];
            }

            foreach ($elementIdsByType as $elementType => $ids)
            {
                $criteria = ['id' => $ids];
                $elements = $this->_getElementQuery($elementType, $criteria)
                    ->all();

                foreach ($elements as $element)
                {
                    $key = array_search($element->id, $elementIds);
                    $elementsToReturn[$key] = $element;
                }
            }

            return $elementsToReturn;
        }
    }

    public function getEntries($paramsOrList = null)
    {
        $params = $this->_convertToParamsArray($paramsOrList, 'list', [
            'type' => Entry::class
        ]);

        return $this->getElements($params);
    }

    public function getUsers($params)
    {
        $params = $this->_convertToParamsArray($paramsOrList, 'list', [
            'type' => User::class
        ]);

        return $this->getElements($params);
    }

    public function getTags($params)
    {
        $params = $this->_convertToParamsArray($paramsOrList, 'list', [
            'type' => Tag::class
        ]);

        return $this->getElements($params);
    }

    public function getCategories($params)
    {
        $params = $this->_convertToParamsArray($paramsOrList, 'list', [
            'type' => Category::class
        ]);

        return $this->getElements($params);
    }

    public function getMatrixBlocks($params)
    {
        $params = $this->_convertToParamsArray($paramsOrList, 'list', [
            'type' => MatrixBlock::class
        ]);

        return $this->getElements($params);
    }

    // Add / Remove
    // =========================================================================

    public function addToList($params)
    {
        $list = $this->_getList($params);
        if(!$list)
        {
            return false;
        }

        $element = $this->_getElement($params);
        $user = $this->_getUser($params);
        $site = $this->_getSite($params);

        // Create Subscription
        $subscription = Listit::$plugin->subscriptions->createSubscription([
            'list' => $list,
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'siteId' => $site->id ?? null,
        ]);

        // Save Subscription
        return Listit::$plugin->subscriptions->saveSubscription($subscription);
    }

    public function removeFromList($params)
    {
        $list = $this->_getList($params);
        if(!$list)
        {
            return false;
        }

        $element = $this->_getElement($params);
        $user = $this->_getUser($params);
        $site = $this->_getSite($params);

        // Subscription
        $subscription = Listit::$plugin->subscriptions->getSubscription([
            'list' => $list,
            'userId' => $user->id ?? null,
            'elementId' => $element->id ?? null,
            'siteId' => $site->id ?? null,
        ]);

        if (!$subscription)
        {
            return true;
        }

        // Delete Subscription
        return Listit::$plugin->subscriptions->deleteSubscription($subscription->id);
    }


    // Favourite
    // =========================================================================

    public function favourite($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::FAVOURITE_LIST_HANDLE
        ]);
        return $this->addToList($params);
    }

    public function unFavourite($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::FAVOURITE_LIST_HANDLE
        ]);
        return $this->removeFromList($params);
    }

    public function isFavourited($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::FAVOURITE_LIST_HANDLE
        ]);
        return $this->isOnList($params);
    }

    public function getFavourites($paramsOrUser)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FAVOURITE_LIST_HANDLE
        ]);
        return $this->getSubscriptions($params);
    }

    public function getFavouritedElements($paramsOrUser)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FAVOURITE_LIST_HANDLE
        ]);
        return $this->getElements($params);
    }


    // Favorite (US Spelling)
    // =========================================================================

    public function favorite($paramsOrElement)
    {
        return $this->favourite($paramsOrElement);
    }

    public function unFavorite($paramsOrElement)
    {
        return $this->unFavourite($paramsOrElement);
    }

    public function isFavorited($paramsOrElement)
    {
        return $this->isFavourited($paramsOrElement);
    }

    public function getFavorites($paramsOrUser)
    {
        return $this->getFavourites($paramsOrUser);
    }

    public function getFavoritedElements($paramsOrUser)
    {
        return $this->getFavouritedElements($paramsOrUser);
    }


    // Like
    // =========================================================================

    public function like($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::LIKE_LIST_HANDLE
        ]);
        return $this->addToList($params);
    }

    public function unLike($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::LIKE_LIST_HANDLE
        ]);
        return $this->removeFromList($params);
    }

    public function isLiked($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::LIKE_LIST_HANDLE
        ]);
        return $this->isOnList($params);
    }

    public function getLikes($paramsOrUser)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::LIKE_LIST_HANDLE
        ]);
        return $this->getSubscriptions($params);
    }

    public function getLikedElements($paramsOrUser)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::LIKE_LIST_HANDLE
        ]);
        return $this->getElements($params);
    }


    // Follow
    // =========================================================================

    public function follow($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);
        return $this->addToList($params);
    }

    public function unFollow($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);
        return $this->removeFromList($params);
    }

    public function isFollowing($paramsOrUser)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'element', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);
        return $this->isOnList($params);
    }

    public function isFollower($paramsOrUser)
    {
        // NOTES : Need to make sure that this handles the reverse stuff here
        //       : user - needs to be the element supplied
        //       : element - needs to be the currently logged in user or the user supplied

        $element = $this->_getUser($paramsOrUser['element'] ?? false);

        // Element supplied
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FOLLOW_LIST_HANDLE,
            'element' => $element
        ]);

        return $this->isOnList($params);
    }

    public function isFriend($paramsOrUser)
    {
        return $this->isFollowing($paramsOrUser) && $this->isFollower($paramsOrUser);
    }

    public function getFollowing($paramsOrUser = null)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);

        $elementIds = $this->getElementIds($params);

        return User::find()
            ->id($elementIds)
            ->all();
    }

    public function getFollowers($paramsOrUser = null)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);

        $ownerIds = $this->getOwnerIds($params);

        return User::find()
            ->id($ownerIds)
            ->all();
    }

    public function getFriends($paramsOrUser = null)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::FOLLOW_LIST_HANDLE
        ]);

        $ownerIds = $this->getOwnerIds($params);
        $elementIds = $this->getElementIds($params);

        return User::find()
            ->id(array_intersect($ownerIds, $elementIds))
            ->all();
    }


    // Star
    // =========================================================================

    public function star($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::STAR_LIST_HANDLE
        ]);

        return $this->addToList($params);
    }

    public function unStar($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::STAR_LIST_HANDLE
        ]);

        return $this->removeFromList($params);
    }

    public function isStared($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::STAR_LIST_HANDLE
        ]);

        return $this->isOnList($params);
    }

    public function getStarredElements($paramsOrUser = null)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::STAR_LIST_HANDLE
        ]);

        return $this->getElements($params);
    }


    // Bookmark
    // =========================================================================

    public function bookmark($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::BOOKMARK_LIST_HANDLE
        ]);

        return $this->addToList($params);
    }

    public function unBookmark($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::BOOKMARK_LIST_HANDLE
        ]);

        return $this->removeFromList($params);
    }

    public function isBookmarked($paramsOrElement)
    {
        $params = $this->_convertToParamsArray($paramsOrElement, 'element', [
            'list' => self::BOOKMARK_LIST_HANDLE
        ]);

        return $this->isOnList($params);
    }

    public function getBookmarkedElements($paramsOrUser = null)
    {
        $params = $this->_convertToParamsArray($paramsOrUser, 'user', [
            'list' => self::BOOKMARK_LIST_HANDLE
        ]);

        return $this->getElements($params);
    }

    // Private Methods
    // =========================================================================

    private function _convertToParamsArray($value, string $key, array $extend = [])
    {
        $params = is_array($value) ? $value : [$key => $value];
        return array_merge($params, $extend);
    }

    private function _getList($paramsOrList = null)
    {
        return is_string($paramsOrList) ? $paramsOrList : ($paramsOrList['list'] ?? false);
    }

    private function _getUser($params = null)
    {
        $user = $params['user'] ?? $params ?? false;
        $user = !$user ? Craft::$app->getUser()->getIdentity() : $user;
        if($user instanceof User)
        {
            return $user;
        }
        return $user && !is_array($user) ? Craft::$app->getUsers()->getUserById((int) $user) : false;
    }

    private function _getElement($params = null)
    {

        $element = $params['element'] ?? $params ?? false;
        if($element instanceof Element)
        {
            return $element;
        }
        return $element && !is_array($element) ? Craft::$app->getElements()->getElementById((int) $element) : false;
    }

    private function _getSite($params = null)
    {
        $site = $params['site'] ?? $params ?? false;
        $site = !$site ? Craft::$app->getSites()->getCurrentSite() : $site;
        if($site instanceof Site)
        {
            return $site;
        }

        return $site && !is_array($site) ? Craft::$app->getSites()->getSiteById((int) $site) : false;
    }

    private function _getElementQuery($elementType, array $criteria): ElementQueryInterface
    {
        $query = $elementType::find();
        Craft::configure($query, $criteria);
        return $query;
    }
}
