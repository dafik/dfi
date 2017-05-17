<?php

namespace Dfi\Asterisk\Stat;

use Dfi\Iface\Helper;
use Dfi\Iface\Model\Pbx\AstConfig;
use Dfi\Iface\Model\Pbx\Context;
use Dfi\Iface\Model\Pbx\Extension;
use Dfi\Iface\Model\Pbx\Priority;
use Dfi\Iface\Provider\Pbx\ExtensionProvider;
use Dfi\Iface\Provider\Pbx\PriorityProvider;
use PropelException;

class Dialplan extends ConfigAbstract
{
    const  FILE_NAME = 'extensions.conf';

    protected static $attributeValues = array(
        'Include' => '',
        'exten' => '',
        'same' => '',
    );

    protected static $categoryField = 'sip.context.name';


    public function __construct($name)
    {

        $this->filename = self::FILE_NAME;
        $this->category = $name;
    }

    /**
     * @param Context $ctx
     * @param $dialplan
     * @throws PropelException
     */
    private static function addEntries(Context $ctx, Dialplan $dialplan)
    {
        $providerName = Helper::getClass("iface.provider.pbx.extension");
        /** @var ExtensionProvider $provider */
        $provider = $providerName::create();


        $extensions = $ctx->getPbxExtensions($provider->orderByRank());
        /** @var $extension Extension */
        foreach ($extensions as $extension) {

            $providerName = Helper::getClass("iface.provider.pbx.priority");
            /** @var PriorityProvider $provider */
            $provider = $providerName::create();


            $priorities = $extension->getPbxPriorities($provider->orderByRank());
            /** @var $priority Priority */
            foreach ($priorities as $priority) {

                $entry = new Entry();

                if ($priorities->isFirst()) {
                    $entry->var_name = 'exten';
                } else {
                    $entry->var_name = 'same';
                }
                $entry->var_val = self::formatLine($priority, $priorities->isFirst());
                $dialplan->addEntry($entry);
            }
            if ($extension->getName() == 'Include') {
                $entry = new Entry();
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

    public static function create(Context $ctx, $addDefaults = false, $doIntersect = false)
    {
        $dialplan = new self($ctx->getName());

        self::addEntries($ctx, $dialplan);
        return $dialplan;
    }

    private static function formatLine(Priority $priority, $isFirst = false)
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

    public function modify(Context $ctx)
    {
        $providerName = Helper::getClass("iface.provider.pbx.astConfig");
        /** @var PriorityProvider $provider */
        $provider = $providerName::create();


        $countProperties = $provider->filterByPbxContext($ctx)->count();
        $countEntries = $this->countEntries();
        if ($countProperties < $countEntries) {
            /** @var AstConfig $entry */
            foreach ($this->getEntriesArray() as $entry) {
                $entry->delete();
            }
        }
        self::addEntries($ctx, $this);
    }


}