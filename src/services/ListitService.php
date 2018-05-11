<?php
namespace fruitstudios\listit\services;

use fruitstudios\listit\Listit;
use fruitstudios\listid\models\Subscription;
use fruitstudios\listid\records\Subscription as SubscriptionRecord;

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

    public function saveSubscription(Subscription $subscription)
    {
        if (!$subscription->validate()) {
            Craft::info('Subscription not saved due to validation error.', __METHOD__);
            return false;
        }

        $subscriptionRecord = SubscriptionRecord::findOne([
            'userId' => $subscription->userId,
            'elementId' => $subscription->elementId,
            'group' => $subscription->group,
            'siteId' => $subscription->siteId
        ]);

        if($existingSubscription) {
            $subscription = $this->_createSubscriptionFromRecord($subscriptionRecord);
            return true;
        }

        $subscriptionRecord = new SubscriptionRecord();
        $subscriptionRecord->setAttributes($subscription->getAttributes(), false);

        // $subscriptionRecord->userId = $subscription->userId;
        // $subscriptionRecord->elementId = $subscription->elementId;
        // $subscriptionRecord->group = $subscription->group;
        // $subscriptionRecord->siteId = $subscription->siteId;

        return $subscriptionRecord->save(false);
    }

    public function deleteSubscription($subscriptionId)
    {
        $subscriptionRecord = SubscriptionRecord::findOne([
            id => $subscriptionId
        ]);

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
     * @param CategoryGroupRecord|null $groupRecord
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
            'group',
            'dateCreated'
        ]));

        return $subscription;
    }

}
