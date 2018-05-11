<?php
namespace fruitstudios\listit\services;

use fruitstudios\listit\Listit;
use fruitstudios\listit\models\Subscription;
use fruitstudios\listit\records\Subscription as SubscriptionRecord;

use Craft;
use craft\base\Component;

use yii\db\StaleObjectException;

class ListitService extends Component
{
    // Public Methods
    // =========================================================================

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
            // TODO: Populate Models
            // https://www.yiiframework.com/doc/guide/2.0/en/db-active-record#active-records-are-called-
            // https://www.yiiframework.com/doc/guide/2.0/en/input-multiple-models
            $subscriptionRecord = SubscriptionRecord::find($criteria);
            return $subscriptionRecords;
        }
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

        return $subscriptionRecord->save(false);
    }

    public function deleteSubscription($subscriptionId)
    {
        $subscriptionRecord = SubscriptionRecord::findOne($subscriptionId);

        if($subscriptionRecord) {
            try {
                $subscriptionRecord->delete();
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

    /**
     * Creates a CategoryGroup with attributes from a CategoryGroupRecord.
     *
     * @param CategoryGroupRecord|null $listRecord
     * @return CategoryGroup|null
     */
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
