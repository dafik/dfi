<?php

class Dfi_Asterisk_Static_Queue extends Dfi_Asterisk_Static_ConfigAbstract
{
    const  FILE_NAME = 'queues.conf';

    protected static $attributeValues = array(
        'musicclass' => 'silence',
        'timeout' => '600',
        'retry' => '1',
        'maxlen' => '0',
        'monitor-format' => 'gsm',
        'strategy' => 'rrmemory',
    );

    protected static $categoryField = 'sip.queue.name';

    protected static $transTable = array(
        'sip.queue.music_class' => 'musicclass',
        'sip.queue.timeout' => 'timeout',
        'sip.queue.retry' => 'retry',
        'sip.queue.max_length' => 'maxlen',
        'sip.queue.monitor_format' => 'monitor-format',
        'sip.queue.strategy' => 'strategy',
    );


    public function __construct($name)
    {
        $this->filename = self::FILE_NAME;
        $this->category = $name;
        foreach ($this->getAttributes() as $attribName) {
            $entry = new Dfi_Asterisk_Static_Entry();
            $entry->var_name = $attribName;
            $entry->var_val = self::$attributeValues[$attribName];
            $this->addEntry($entry);
        }
    }


    public function getName()
    {
        return $this->category;
    }

    public static function create(PbxQueue $queue)
    {
        $pbxQueue = parent::create($queue);

        if ($queue->getDefinition()) {
            $def = explode("\n", $queue->getDefinition());
            foreach ($def as $definition) {
                if (false !== strpos($definition, '=>')) {
                    list($name, $val) = explode('=>', $definition);
                    $entry = new Dfi_Asterisk_Static_Entry();
                    $entry->var_name = trim($name);
                    $entry->var_val = trim($val);
                    $pbxQueue->addEntry($entry);
                }
            }
        }

        return $pbxQueue;
    }

    /*    private function generalConfig()
        {
            $c = array(
                'general' => array(
                    'persistentmembers' => 'yes',
                    'autofill' => 'yes',
                    'monitor-type' => 'MixMonitor',
                    'shared_lastcall' => 'yes',
                ));
        }*/
}