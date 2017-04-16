<?

namespace Dfi\Asterisk;

use Dfi\Iface\Provider\Pbx\QueueProvider;

class Helper
{
    public static function getQueuesList()
    {

        $queueProviderName = \Dfi\Iface\Helper::getClass("iface.provider.pbx.queue");
        /** @var QueueProvider $queueProvider */
        $queueProvider = $queueProviderName::create();

        $queuesPredictive = $queueProvider
            ->filTerByType('predictive')
            ->filterByIsActive(true)
            ->select(array('Name'))
            ->find()
            ->toArray();

        /** @var QueueProvider $queueProvider */
        $queueProvider = $queueProviderName::create();
        $queuesIncoming = $queueProvider
            ->filTerByType('incoming')
            ->filterByIsActive(true)
            ->select(array('Name'))
            ->find()
            ->toArray();

        return array(
            'incoming' => $queuesIncoming,
            'predictive' => $queuesPredictive
        );
    }
}