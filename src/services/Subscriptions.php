<?php
namespace fruitstudios\listit\services;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;
use fruitstudios\listit\records\Subscription as SubscriptionRecord;
use fruitstudios\listit\events\SubscriptionEvent;

use Craft;
use craft\base\Component;
use craft\db\Query;

use yii\db\StaleObjectException;

class Subscriptions extends Component
{
    // Constants
    // =========================================================================

    const EVENT_ADDED_TO_LIST = 'addedToList';
    const EVENT_REMOVED_FROM_LIST = 'removedFromList';

    // Public Methods
    // =========================================================================

    public function createSubscription($attributes = [])
    {
        $subscription = new Subscription();
        $subscription->setAttributes($attributes);
        return $subscription;
    }

    public function getSubscription(array $criteria = null)
    {
        $subscriptionRecord = SubscriptionRecord::findOne($criteria);
        return $this->_createSubscriptionFromRecord($subscriptionRecord);
    }

    public function getSubscriptions(array $criteria = [], array $select = null)
    {
        if($select)
        {
            return (new Query())
                ->select($select)
                ->from([SubscriptionRecord::tableName()])
                ->where($criteria)
                ->all();
        }
        else
        {
            $subscriptionRecords = SubscriptionRecord::find($criteria);

            $subscriptionModels = [];
            if($subscriptionModels)
            {
                foreach ($subscriptionRecords as $subscriptionRecord)
                {
                    $subscriptionModels[] = $this->createSubscriptionFromRecord($subscriptionRecord);
                }
            }
            return $subscriptionModels;
        }
    }

    public function getSubscriptionsColumn(array $criteria = [], string $column)
    {
        return (new Query())
            ->select($column)
            ->from([SubscriptionRecord::tableName()])
            ->where($criteria)
            ->column();
    }

    public function saveSubscription(Subscription $subscription)
    {
        if (!$subscription->validate()) {
            Craft::info('Subscription not saved due to validation error.', __METHOD__);
            return false;
        }

        $subscriptionRecord = SubscriptionRecord::findOne([
            'userId' => $subscription->userId,
            'elementId' => $subscription->elementId,
            'list' => $subscription->list,
            'siteId' => $subscription->siteId
        ]);

        if($subscriptionRecord) {
            $subscription = $this->_createSubscriptionFromRecord($subscriptionRecord);
            return true;
        }

        $subscriptionRecord = new SubscriptionRecord();
        $subscriptionRecord->setAttributes($subscription->getAttributes(), false);
        if(!$subscriptionRecord->save(false))
        {
            return false;
        }

        $subscriptionModel = $this->_createSubscriptionFromRecord($subscriptionRecord);

        $this->trigger(self::EVENT_ADDED_TO_LIST, new SubscriptionEvent([
            'subscription' => $subscriptionModel
        ]));

        return true;
    }

    public function deleteSubscription($subscriptionId)
    {
        $subscriptionRecord = SubscriptionRecord::findOne($subscriptionId);

        if($subscriptionRecord) {
            try {

                $subscriptionModel = $this->_createSubscriptionFromRecord($subscriptionRecord);
                $subscriptionRecord->delete();

                $this->trigger(self::EVENT_REMOVED_FROM_LIST, new SubscriptionEvent([
                    'subscription' => $subscriptionModel
                ]));

            } catch (StaleObjectException $e) {
                Craft::error($e->getMessage(), __METHOD__);
            } catch (\Exception $e) {
                Craft::error($e->getMessage(), __METHOD__);
            } catch (\Throwable $e) {
                Craft::error($e->getMessage(), __METHOD__);
            }
        }

        return true;
    }

    // Private Methods
    // =========================================================================

    private function _createSubscriptionFromRecord(SubscriptionRecord $subscriptionRecord = null)
    {
        if (!$subscriptionRecord) {
            return null;
        }

        $subscription = new Subscription($subscriptionRecord->toArray([
            'id',
            'userId',
            'elementId',
            'siteId',
            'list',
            'dateCreated'
        ]));

        return $subscription;
    }

}