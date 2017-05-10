<?php
namespace Dfi\Filter;

use Zend_Filter_Null;

class NullFilter extends Zend_Filter_Null
{
    const BOOLEAN = 1;
    const INTEGER = 2;
    const EMPTY_ARRAY = 4;
    const STRING = 8;
    const ZERO = 16;
    const TEXT = 32;
    const ALL = 63;

    protected $_constants = array(
        self::BOOLEAN => 'boolean',
        self::INTEGER => 'integer',
        self::EMPTY_ARRAY => 'array',
        self::STRING => 'string',
        self::ZERO => 'zero',
        self::TEXT => 'text',
        self::ALL => 'all'
    );

    /**
     * Internal type to detect
     *
     * @var integer
     */
    protected $_type = self::ALL;

    /**
     * Defined by Zend_Filter_Interface
     *
     * Returns null representation of $value, if value is empty and matches
     * types that should be considered null.
     *
     * @param  string $value
     * @return string
     */
    public function filter($value)
    {
        $type = $this->getType();

        // STRING ZERO ('0')
        if ($type >= self::TEXT) {
            //$type -= self::TEXT;
            if (is_string($value) && (strtolower($value) == 'null')) {
                return null;
            }
        }
        return parent::filter($value);
    }
}
