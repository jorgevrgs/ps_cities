<?php
/**
 * 2007-2018 PrestaShop.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * https://opensource.org/licenses/OSL-3.0
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@prestashop.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade PrestaShop to newer
 * versions in the future. If you wish to customize PrestaShop for your
 * needs please refer to http://www.prestashop.com for more information.
 *
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2018 PrestaShop SA
 * @license   https://opensource.org/licenses/OSL-3.0 Open Software License (OSL 3.0)
 * International Registered Trademark & Property of PrestaShop SA
 */

/**
 * Class StockPickingCore.
 */
class StockPicking extends ObjectModel
{
    public $date_ordered;
    public $id_order;
    public $id_product;
    public $id_product_attribute;
    public $id_supplier;
    public $invoice;
    public $quantity;
    public $serial;
    public $date_expected;
    public $date_delivered;


    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'city',
        'primary' => 'id_stock_picking',
        'fields' => array(
            'id_order' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_product_attribute' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_supplier' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),

            'invoice' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt'),
            'quantity' => array('type' => self::TYPE_STRING, 'validate' => 'isUnsignedInt', 'required' => true),
            'serial' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true),

            'date_ordered' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_expected' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
            'date_delivered' => array('type' => self::TYPE_DATE, 'validate' => 'isDate'),
        ),
    );
}
