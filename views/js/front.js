/**
 * 2007-2018 PrestaShop
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
 *  @copyright 2007-2018 PrestaShop SA
 *  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 *
 * Don't forget to prefix your containers with your own identifier
 * to avoid any conflicts with others containers.
 */

"use strict";

//import $ from 'jquery';
//import prestashop from 'prestashop';

function ajaxCities(selectors) {
  $("body").on("change", selectors.state, () => {
    var requestData = {
      id_city: $(selectors.city).val(),
      id_state: $(selectors.state).val(),
      id_country: $(selectors.country).val(),
      id_address: $(selectors.address).val(),
      ajax: 1,
      action: "cities",
    };

    id_city_selected = $(selectors.city).val();

    $.post(cities_controller, requestData).then(function (html) {
      if (html == "false") {
        $(selectors.city + " option[value=0]").attr("selected", "selected");
      } else {
        $(selectors.city).html(html);
        $(selectors.city + " option[value=" + id_city_selected + "]").attr(
          "selected",
          "selected"
        );
      }
    });
  });

  $("body").on("change", selectors.city, () => {
    var city_selected = $(selectors.city + " option:selected").text();
    $("input[name=city]").val(city_selected);
  });
}

$(document).ready(() => {
  ajaxCities({
    city: "select[name=id_city]",
    state: "select[name=id_state]",
    country: "select[name=id_country]",
    address: "select[name=id_address]",
  });
});
