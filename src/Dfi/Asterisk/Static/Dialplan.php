<?php

class Dfi_Asterisk_Static_Dialplan extends Dfi_Asterisk_Static_ConfigAbstract
{
    const  FILE_NAME = 'extensions.conf';

    protected static $attributeValues = array(
        'Include' => '',
        'exten' => '',
        'same' => '',
    );

    protected static $categoryField = 'sip.context.name';

    protected static $transTable = array();

    public function __construct($name)
    {
        $this->filename = self::FILE_NAME;
        $this->category = $name;
        $this->allowDuplicateKeys = true;
    }

    public function getName()
    {
        return $this->category;
    }

    public static function create(PbxContext $ctx)
    {
        $dialplan = new self($ctx->getName());

        $extensions = $ctx->getPbxExtensions(PbxExtensionQuery::create()->orderByRank());
        /** @var $extension PbxExtension */
        foreach ($extensions as $extension) {
            $priorities = $extension->getPbxPriorities(PbxPriorityQuery::create()->orderByRank());
            /** @var $priority PbxPriority */
            foreach ($priorities as $priority) {

                $entry = new Dfi_Asterisk_Static_Entry();
                if ($extension->getName() == 'Include') {
                    $entry->var_name = 'Include';
                } elseif ($priorities->isFirst()) {
                    $entry->var_name = 'exten';
                } else {
                    $entry->var_name = 'same';
                }
                $entry->var_val = self::formatLine($priority, $priorities->isFirst());
                $dialplan->addEntry($entry);
            }
        }
        return $dialplan;
    }

    private static function formatLine(PbxPriority $priority, $isFirst = false)
    {
        $extension = $priority->getPbxExtension();

        if ($extension->getName() == 'Include') {
            $value = $priority->getApp();
        } else {
            $value = $priority->getApp() . '(' . $priority->getData() . ')';
            if ($isFirst) {
                $value = '1,' . $value;
            }
            if ($priority->getLabel()) {
                $value = '(' . $priority->getLabel() . '),' . $value;
            }
            if ($isFirst) {
                $value = $extension->getName() . ',' . $value;
            } else {
                if ($priority->getLabel()) {
                    $value = 'n' . $value;
                } else {
                    $value = 'n,' . $value;
                }
            }
        }
        return $value;
    }

    public function modify(PbxContext $ctx)
    {
        $countProperties = PbxPriorityQuery::create()
            ->filterByPbxContext($ctx)->count();
        $countEntries = $this->countEntries();
        if ($countProperties < $countEntries) {
            for ($i = ($countProperties - 1); $i < $countEntries; $i++) {
                $entry = $this->getEntry($i);
                if ($entry) {
                    $entry->delete();
                } else {
                    //TODO $x = 'błąd';
                }
            }
        }

        $key = 0;
        $extensions = $ctx->getPbxExtensions(PbxExtensionQuery::create()->orderByRank());
        /** @var $extension PbxExtension */
        foreach ($extensions as $extension) {
            $priorities = $extension->getPbxPriorities(PbxPriorityQuery::create()->orderByRank());
            /** @var $priority PbxPriority */
            foreach ($priorities as $priority) {
                $entry = $this->getEntry($key);
                $new = false;
                if (!$entry) {
                    $entry = new Dfi_Asterisk_Static_Entry();
                    $new = true;
                }

                if ($extension->getName() == 'Include') {
                    $var_name = 'Include';
                } elseif ($priorities->isFirst()) {
                    $var_name = 'exten';
                } else {
                    $var_name = 'same';
                }

                if ($var_name != $entry->var_name) {
                    $entry->updateName($var_name);
                }

                $var_val = self::formatLine($priority, $priorities->isFirst());

                if ($var_val != $entry->var_val) {
                    $entry->updateValue($var_val);
                }
                if ($new) {
                    $this->addEntry($entry);
                }

                $key++;
            }
        }
        if ($this->category != $ctx->getName()) {
            $this->setCategory($ctx->getName());
        }
    }

   /* private function generalConfig()
    {
        $c = array(
            'general' => array(
                'static' => 'yes',
                'writeprotect' => 'no',
                'clearglobalvars' => 'no'
            ));
    }*/


}