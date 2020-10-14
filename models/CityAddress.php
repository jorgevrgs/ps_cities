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
 * Class CityCore.
 */
class CityAddress extends ObjectModel
{
    /** @var int State id which city belongs */
    public $id_city;

    /** @var int Address id which city belongs */
    public $id_address;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'city_address',
        'primary' => 'id_city_address',
        'fields' => array(
            'id_city' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_address' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
        ),
    );

    protected $webserviceParameters = array(
        'fields' => array(
            'id_city' => array('xlink_resource' => 'cities'),
            'id_address' => array('xlink_resource' => 'addresses'),
        ),
    );

    public function getIdCityByIdAddress(int $idAddress)
    {
        $cache_key = 'CityAddress::getIdCityByIdAddress_' . (int) $idAddress;
        if (!Cache::isStored($cache_key)) {
            $result = (int) Db::getInstance()->getValue('
    			SELECT `id_city`
    			FROM `'._DB_PREFIX_.'city_address`
    			WHERE `id_address` = \''.(int) $idAddress.'\'
    		');

            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    public static function getIsoByIdAddress($idAddress)
    {
        if (empty($idAddress)) {
            return false;
        }

        $cache_key = 'CityAddress::getIsoByIdAddress_' . (int) $idAddress;
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance()->getValue('
    			SELECT c.`iso_code`
    			FROM `'._DB_PREFIX_.'city` c
                LEFT JOIN `'._DB_PREFIX_.'city_address` ca ON c.`id_city` = ca.`id_city`
    			WHERE ca.`id_address` = \''.(int) $idAddress.'\'
    		');

            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    public static function getCityAddressByIdAddress(int $idAddress)
    {
        $cache_key = 'CityAddress::getCityAddressByIdAddress_' . (int) $idAddress;
        if (!Cache::isStored($cache_key)) {
            $result = (int) Db::getInstance()->getValue('
    			SELECT `id_city_address`
    			FROM `'._DB_PREFIX_.'city_address`
    			WHERE `id_address` = \''.(int) $idAddress.'\'
    		');

            Cache::store($cache_key, $result);
        } else {
            $result = Cache::retrieve($cache_key);
        }

        return new self((int) $result);
    }
}
