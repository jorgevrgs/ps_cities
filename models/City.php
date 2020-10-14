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
class City extends ObjectModel
{
    /** @var int State id which city belongs */
    public $id_state;
    /** @var int Zone id which city belongs */
    public $id_zone;
    /** @var string 2 letters iso code */
    public $iso_code;
    /** @var string Name */
    public $name;
    /** @var bool Status for delivery */
    public $active = true;

    /**
     * @see ObjectModel::$definition
     */
    public static $definition = array(
        'table' => 'city',
        'primary' => 'id_city',
        'fields' => array(
            'id_country' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_state' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'id_zone' => array('type' => self::TYPE_INT, 'validate' => 'isUnsignedId', 'required' => true),
            'iso_code' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 7),
            'name' => array('type' => self::TYPE_STRING, 'validate' => 'isGenericName', 'required' => true, 'size' => 32),
            'active' => array('type' => self::TYPE_BOOL, 'validate' => 'isBool'),
        ),
    );

    protected $webserviceParameters = array(
        'fields' => array(
            'id_zone' => array('xlink_resource' => 'zones'),
            'id_state' => array('xlink_resource' => 'states'),
            'id_country' => array('xlink_resource' => 'countries'),
        ),
    );

    public static function getCities($active = false)
    {
        $cacheId = 'City::getCities_'.(int) $active;
        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS('
    		SELECT `id_city`, `id_state`, `id_country`, `id_zone`, `iso_code`, `name`, `active`
    		FROM `'._DB_PREFIX_.'city`
    		'.($active ? 'WHERE active = 1' : '').'
    		ORDER BY `name` ASC');
            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Get a city name with its ID.
     *
     * @param int $idCity City ID
     *
     * @return string City name
     */
    public static function getNameById($idCity)
    {
        if (!$idCity) {
            return false;
        }

        $cacheId = 'City::getNameById_'.(int) $idCity;
        if (!Cache::isStored($cacheId)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
				SELECT `name`
				FROM `'._DB_PREFIX_.'city`
				WHERE `id_city` = '.(int) $idCity
            );

            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Get City ID with its name.
     *
     * @param string $city State ID
     *
     * @return int city id
     */
    public static function getIdByName($name)
    {
        if (empty($city)) {
            return false;
        }

        $cacheId = 'City::getIdByName_'.pSQL($name);
        if (!Cache::isStored($cacheId)) {
            $result = (int) Db::getInstance()->getValue('
				SELECT `id_city`
				FROM `'._DB_PREFIX_.'city`
				WHERE `name` = \''.pSQL($name).'\'
			');

            Cache::store($cacheId, $result);

            return $result;
        }

        return Cache::retrieve($cacheId);
    }

    /**
     * Get a city id with its iso code.
     *
     * @param string $isoCode Iso code
     *
     * @return int city id
     */
    public static function getIdByIso($isoCode, $idState = null)
    {
        $cache_key = 'City::getIdByIso_'. pSQL($isoCode).'_'.(int)$idState;
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance()->getValue('
    		SELECT `id_city`
    		FROM `'._DB_PREFIX_.'city`
    		WHERE `iso_code` = \''.pSQL($isoCode).'\'
    		'.($idState ? 'AND `id_state` = '.(int) $idState : ''));

            Cache::store($cache_key, $result);
        } else {
           $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    /**
     * Delete a city only if is not in use.
     *
     * @return bool
     */
    public function delete()
    {
        if (!$this->isUsed()) {
            // Database deletion
            $result = Db::getInstance()->delete($this->def['table'], '`'.$this->def['primary'].'` = '.(int) $this->id);
            if (!$result) {
                return false;
            }
            // Database deletion for multilingual fields related to the object
            if (!empty($this->def['multilang'])) {
                Db::getInstance()->delete(bqSQL($this->def['table']).'_lang', '`'.$this->def['primary'].'` = '.(int) $this->id);
            }

            return $result;
        }

        return false;
    }

    /**
     * Check if a city is used.
     *
     * @return bool
     */
    public function isUsed()
    {
        return $this->countUsed() > 0;
    }

    /**
     * Returns the number of utilisation of a state.
     *
     * @return int count for this state
     */
    public function countUsed()
    {
        $cache_key = 'City::countUsed';
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
    			SELECT COUNT(*)
    			FROM `'._DB_PREFIX_.'address`
    			WHERE `'.$this->def['primary'].'` = '.(int) $this->id
            );

            Cache::store($cache_key, $result);
        } else {
           $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    /**
     * Get cities by State ID.
     *
     * @param int $idState State ID
     *
     * @return array|false|mysqli_result|null|PDOStatement|resource
     */
    public static function getCitiesByIdState($idState)
    {
        if (empty($idState)) {
            return false;
        }

        $cache_key = 'City::getCitiesByIdState_' . (int) $idState;
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance()->executeS('
    			SELECT *
    			FROM `'._DB_PREFIX_.'city` c
    			WHERE c.`id_state` = '.(int) $idState.';'
            );

            Cache::store($cache_key, $result);
        } else {
           $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    public static function getCityByNameAndIdState(string $name, int $id_state)
    {
        $cache_key = 'City::getCityByNameAndIdState_' . pSQL($name). '_' . (int) $id_state;
        if (!Cache::isStored($cache_key)) {
            $result = (int) Db::getInstance()->getValue('
    			SELECT `id_city`
    			FROM `'._DB_PREFIX_.'city`
    			WHERE `name` = \''.pSQL($name).'\'
                AND `id_state` = '.(int) $id_state.'
    		');

            Cache::store($cache_key, $result);
        } else {
           $result = Cache::retrieve($cache_key);
        }

        return new self((int) $result);
    }

    /**
     * Get Zone ID.
     *
     * @param int $idCity City ID
     *
     * @return false|null|string
     */
    public static function getIdZone($idCity)
    {
        if (!Validate::isUnsignedId($idCity)) {
            die(Tools::displayError());
        }

        $cache_key = 'City::getIdZone_' . (int) $idCity;
        if (!Cache::isStored($cache_key)) {
            $result = Db::getInstance(_PS_USE_SQL_SLAVE_)->getValue('
    			SELECT `id_zone`
    			FROM `'._DB_PREFIX_.'city`
    			WHERE `id_city` = '.(int) $idCity
            );

            Cache::store($cache_key, $result);
        } else {
           $result = Cache::retrieve($cache_key);
        }

        return $result;
    }

    /**
     * @param array $idsCities City IDs
     * @param int   $idZone    Zone ID
     *
     * @return bool
     */
    public function affectZoneToSelection($idsCities, $idZone)
    {
        // cast every array values to int (security)
        $idsCities = array_map('intval', $idsCities);

        return Db::getInstance()->execute('
		UPDATE `'._DB_PREFIX_.'city` SET `id_zone` = '.(int) $idZone.' WHERE `id_city` IN ('.implode(',', $idsCities).')
		');
    }
}
