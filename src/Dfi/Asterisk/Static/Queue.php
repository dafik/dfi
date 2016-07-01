<?php

class Dfi_Asterisk_Static_Queue extends Dfi_Asterisk_Static_ConfigAbstract
{
    const  FILE_NAME = 'queues.conf';

    protected $allowDuplicateKeys = true;

    protected static $attributeValues = array(
        'musicclass' => 'silence',
        'timeout' => '600',
        'retry' => '1',
        'maxlen' => '0',
        'monitor-format' => 'gsm',
        'strategy' => 'rrmemory',
    );

    protected static $categoryField = 'pbx_queues.name';

    protected static $transTable = array(
        'pbx_queues.music_class' => 'musicclass',
        'pbx_queues.timeout' => 'timeout',
        'pbx_queues.retry' => 'retry',
        'pbx_queues.max_length' => 'maxlen',
        'pbx_queues.monitor_format' => 'monitor-format',
        'pbx_queues.strategy' => 'strategy',
    );


    public function __construct($name)
    {
        parent::__construct();
        
        $this->filename = self::FILE_NAME;
        $this->category = $name;
    }


    public function getName()
    {
        return $this->category;
    }

    public static function create(PbxQueue $queue)
    {
        $pbxQueue = parent::create($queue);
        $pbxQueue->applyDefinitions($queue->getDefinition());
        return $pbxQueue;
    }
}