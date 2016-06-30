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

    /**
     * @param PbxContext $ctx
     * @param $dialplan
     * @throws PropelException
     */
    private static function addEntries(PbxContext $ctx, $dialplan)
    {
        $extensions = $ctx->getPbxExtensions(PbxExtensionQuery::create()->orderByRank());
        /** @var $extension PbxExtension */
        foreach ($extensions as $extension) {
            $priorities = $extension->getPbxPriorities(PbxPriorityQuery::create()->orderByRank());
            /** @var $priority PbxPriority */
            foreach ($priorities as $priority) {

                $entry = new Dfi_Asterisk_Static_Entry();

                if ($priorities->isFirst()) {
                    $entry->var_name = 'exten';
                } else {
                    $entry->var_name = 'same';
                }
                $entry->var_val = self::formatLine($priority, $priorities->isFirst());
                $dialplan->addEntry($entry);
            }
            if ($extension->getName() == 'Include') {
                $entry = new Dfi_Asterisk_Static_Entry();
                $entry->var_name = 'Include';
                $entry->var_val = $extension->getInclude();
                $dialplan->addEntry($entry);

            }
        }
    }

    public function getName()
    {
        return $this->category;
    }

    public static function create(PbxContext $ctx)
    {
        $dialplan = new self($ctx->getName());

        self::addEntries($ctx, $dialplan);
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
        self::addEntries($ctx, $this);
    }


}