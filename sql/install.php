<?php
/**
* 2007-2020 PrestaShop
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
*  @copyright 2007-2020 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

$sql = array();

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'city` (
    `id_city` int(11) NOT NULL AUTO_INCREMENT,
    `id_country` int(10) UNSIGNED NOT NULL,
    `id_state` int(10) UNSIGNED NOT NULL,
    `id_zone` int(10) UNSIGNED NOT NULL,
    `iso_code` varchar(7) NOT NULL,
    `name` varchar(64) NOT NULL,
    `active` tinyint(1) UNSIGNED NOT NULL DEFAULT "0",
    PRIMARY KEY  (`id_city`),
    KEY `name` (`name`),
    KEY `id_state` (`id_state`),
    KEY `id_country` (`id_country`),
    KEY `id_zone` (`id_zone`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

$sql[] = 'CREATE TABLE IF NOT EXISTS `'._DB_PREFIX_.'city_address` (
    `id_city_address` int(11) NOT NULL AUTO_INCREMENT,
    `id_city` int(11) UNSIGNED NOT NULL,
    `id_address` int(11) UNSIGNED NOT NULL,
    PRIMARY KEY  (`id_city_address`),
    KEY `id_city` (`id_city`),
    KEY `id_address` (`id_address`)
) ENGINE='._MYSQL_ENGINE_.' DEFAULT CHARSET=utf8;';

foreach ($sql as $query) {
    if (false == Db::getInstance()->execute($query)) {
        return false;
    }
}
