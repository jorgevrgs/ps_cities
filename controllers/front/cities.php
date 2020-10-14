<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/
class Ps_CitiesCitiesModuleFrontController extends ModuleFrontController
{
    //public $content_only = true;
    //WHERE ci.id_country = '.(int) (Tools::getValue('id_country')).'
    //LEFT JOIN '._DB_PREFIX_.'country co ON (ci.`id_country` = co.`id_country`)

    protected function displayAjaxCities()
    {
        /*
        $address = new Address(Tools::getValue('id_address'));
        if (Validate::isLoadedObject($address)
        && !empty($address->id_state)
        && !Tools::getIsset('id_state')) {
            $id_state = $address->id_state;
        } else {
            $id_state = Tools::getValue('id_state');
        }
        $id_state
        (Tools::getValue('id_state'))
        */

        $cities = Db::getInstance()->executeS('
		SELECT c.id_city, c.name
		FROM '._DB_PREFIX_.'city c
        LEFT JOIN '._DB_PREFIX_.'state s ON (s.`id_state` = c.`id_state`)
        LEFT JOIN '._DB_PREFIX_.'country co ON (co.`id_country` = c.`id_country`)
        WHERE c.id_state = '.(int) (Tools::getValue('id_state')).'
        AND c.id_country = '.(int) (Tools::getValue('id_country')).'
        AND c.active = 1
		ORDER BY c.`name` ASC');

        if (is_array($cities) && !empty($cities)) {
            $list = '';
            if (true != (bool) Tools::getValue('no_empty')) {
                $empty_value = (Tools::isSubmit('empty_value')) ? Tools::getValue('empty_value') : '-';
                $list = '<option value="0">'.Tools::htmlentitiesUTF8($empty_value).'</option>'."\n";
            }

            foreach ($cities as $city) {
                $list .= '<option value="'.(int) ($city['id_city']).'"'.((isset($_GET['id_city']) and $_GET['id_city'] == $city['id_city']) ? ' selected="selected"' : '').'>'
                    .$city['name']
                .'</option>'."\n";
            }
        } else {
            $list = 'false';
        }

        die($list);
    }
}
