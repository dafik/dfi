<?

class Dfi_Asterisk_Helper
{
    public static function getQueuesList()
    {

        $queuesPredictive = PbxQueueQuery::create()
            ->filTerByType('predictive')
            ->filterByIsActive(true)
            ->select(array('Name'))
            ->find()
            ->toArray();

        $queuesIncoming = PbxQueueQuery::create()
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