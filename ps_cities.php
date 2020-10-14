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

if (!defined('_PS_VERSION_')) {
    exit;
}

// use PrestaShop\PrestaShop\Core\Grid\Definition\GridDefinitionInterface;
// use PrestaShop\PrestaShop\Core\Search\Filters\CustomerFilters;
// use Doctrine\DBAL\Query\QueryBuilder;
// use Symfony\Component\Form\FormBuilderInterface;

// require_once _PS_MODULE_DIR_.'ps_cities/models/City.php';
// require_once _PS_MODULE_DIR_.'ps_cities/models/CityAddress.php';
require_once _PS_MODULE_DIR_.'ps_cities/vendor/autoload.php';

class Ps_cities extends Module
{
    protected $config_form = false;
    protected $tabs = [
        [
            'name' => 'Cities',
            'visible' => true,
            'class_name' => 'AdminCities',
            'parent_class_name' => 'AdminParentCountries',
            'icon' => 'city',
        ],
    ];

    public function __construct()
    {
        $this->name = 'ps_cities';
        $this->tab = 'front_office_features';
        $this->version = '0.0.1';
        $this->author = 'Jorge Vargas';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Cities');
        $this->description = $this->l('Cities module for PrestaShop 1.7.7.X');

        $this->ps_versions_compliancy = array('min' => '1.7', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        if (false == extension_loaded('curl')) {
            $this->_errors[] = $this->l('You have to enable the cURL extension on your server to install this module');

            return false;
        }

        $return = parent::install();

        $return &= $this->installTabs();

        // include dirname(__FILE__).'/sql/install.php';

        $hooks = [
            'displayHeader',
            'displayBackOfficeHeader',

            // Form
            'additionalCustomerAddressFields',
            'actionValidateCustomerAddressForm',
            'actionSubmitCustomerAddressForm',

            // Grids
            'actionAddressGridDefinitionModifier',
            'actionAddressGridQueryBuilderModifier',
            'actionAddressFormBuilderModifier',
            'actionAfterCreateAddressFormHandler',
            'actionAfterUpdateAddressFormHandler',
        ];

        $return = true;

        foreach ($hooks as $hook) {
            $return &= $this->registerHook($hook);
        }

        return $return;
    }

    public function uninstall()
    {
        // include(dirname(__FILE__).'/sql/uninstall.php');

        return parent::uninstall();
    }

    public function installTabs()
    {
        $this->uninstallTabs();
        $languages = Language::getLanguages(false);
        $tabs = $this->getTabs();
        foreach ($tabs as $tabDetails) {
            try {
                if (!Tab::getIdFromClassName($tabDetails['class_name'])) {
                    $tab = new Tab();
                    $tab->active = isset($tabDetails['visible']) ? $tabDetails['visible'] : true;
                    $tab->class_name = $tabDetails['class_name'];
                    $tab->module = $this->name;
                    foreach ($languages as $lang) {
                        $tab->name[(int) $lang['id_lang']] = $this->l($tabDetails['name']);
                    }
                    $tab->icon = isset($tabDetails['icon']) ? $tabDetails['icon'] : null;
                    $tab->id_parent = Tab::getIdFromClassName($tabDetails['parent_class_name']);
                    if (!$tab->save()) {
                        throw new Exception(
                            $this->translator->trans(
                                'Failed to install admin tab "%name%".',
                                array('%name%' => $tab->name),
                                'Admin.Modules.Notification'
                            )
                        );
                    }
                }
            } catch (Exception $e) {
                $this->errors[] = $e->getMessage();
            }
        }

        return true;
    }

    public function uninstallTabs()
    {
        $tabs = $this->getTabs();

        $result = true;
        foreach ($tabs as $tab) {
            $id_tab = (int) Tab::getIdFromClassName($tab['class_name']);
            $object = new Tab($id_tab);
            $result &= $object->delete();
        }

        return $result;
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitPs_citiesModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitPs_citiesModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'switch',
                        'label' => $this->l('Live mode'),
                        'name' => 'PS_CITIES_LIVE_MODE',
                        'is_bool' => true,
                        'desc' => $this->l('Use this module in live mode'),
                        'values' => array(
                            array(
                                'id' => 'active_on',
                                'value' => true,
                                'label' => $this->l('Enabled')
                            ),
                            array(
                                'id' => 'active_off',
                                'value' => false,
                                'label' => $this->l('Disabled')
                            )
                        ),
                    ),
                    array(
                        'col' => 3,
                        'type' => 'text',
                        'prefix' => '<i class="icon icon-envelope"></i>',
                        'desc' => $this->l('Enter a valid email address'),
                        'name' => 'PS_CITIES_ACCOUNT_EMAIL',
                        'label' => $this->l('Email'),
                    ),
                    array(
                        'type' => 'password',
                        'name' => 'PS_CITIES_ACCOUNT_PASSWORD',
                        'label' => $this->l('Password'),
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'PS_CITIES_LIVE_MODE' => Configuration::get('PS_CITIES_LIVE_MODE', true),
            'PS_CITIES_ACCOUNT_EMAIL' => Configuration::get('PS_CITIES_ACCOUNT_EMAIL', 'contact@prestashop.com'),
            'PS_CITIES_ACCOUNT_PASSWORD' => Configuration::get('PS_CITIES_ACCOUNT_PASSWORD', null),
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
    }

    public function hookUpdateCarrier($params)
    {
        /**
         * Not needed since 1.5
         * You can identify the carrier by the id_reference
        */
    }

    public function hookDisplayAdminStatsGridEngine()
    {
        /* Place your code here. */
    }

    public function hookDisplayHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
        $this->context->controller->addCSS($this->_path.'/views/css/front.css');
        Media::AddJsDef(['cities_controller' => $this->context->link->getModuleLink('ps_cities', 'cities')]);
    }

    /**
     * @see /classes/form/CustomerAddressFormatter.php#L156
     * @param array $param [
     *   @var array $fields
     * ]
     */
    public function hookAdditionalCustomerAddressFields($params)
    {
        ($params['fields']['city'])->setType('hidden');

        $formField = $params['fields'];
        $formField = (new FormField())
        ->setName('id_city')
        ->setLabel($this->l('City'))
        ->setRequired(true)
        ->setType('select')
        ;

        if (Tools::getIsset('id_address')) {
            $address = new Address(Tools::getValue('id_address'));

            if (!empty($address->id_state)) {
                $cities = City::getCitiesByIdState((int) $address->id_state);

                if (!empty($cities)) {
                    foreach ($cities as $city) {
                        $formField->addAvailableValue(
                            $city['id_city'],
                            $city['name']
                        );
                    }

                    $id_city = CityAddress::getIdCityByIdAddress((int) $address->id);
                    $formField->setValue($id_city);
                }
            }
        }

        $keys = array_keys($params['fields']);

        $search = 'city';
        foreach ($keys as $key => $value) {
            if ($value == $search) {
                break;
            }
        }

        $part1 = array_slice($params['fields'], 0, $key + 1);
        $part2 = array_slice($params['fields'], $key + 1);

        $part1['id_city'] = $formField;

        $params['fields'] = array_merge($part1, $part2);
    }

    /**
     * @see /classes/form/CustomerAddressForm.php#L123
     * @param array $param [
     *   @var CustomerAddressForm $form
     * ]
     */
    public function hookActionValidateCustomerAddressForm($params)
    {
        if (empty(Tools::getValue('id_city'))
        || empty(Tools::getValue('city'))) {
            return false;
        }

        $form = $params['form'];

        $idCityField = $form->getField('id_city');

        $idCity = (int) Tools::getValue('id_city');
        $cityObj = new City($idCity);

        $city = pSQL(Tools::getValue('city'));

        if ($cityObj->name !== $city) {
            $idCityField->addError(sprintf(
                $this->l('Invalid name in field id_city %s and city %s'),
                $cityObj->name,
                $city
            ));

            return false;
        }

        return true;
    }

    /**
     * @see /classes/form/CustomerAddressForm.php#L153
     * @param array $param [
     *   @var Address $address
     * ]
     */
    public function hookActionSubmitCustomerAddressForm($params)
    {
        /** @var Address */
        $address = $params['address'];
        $address->save();

        if (!Validate::isLoadedObject($address)) {
            throw new PrestaShopException($this->l('Address object error while trying to save city'));
        }

        $cityAddress = CityAddress::getCityAddressByIdAddress((int) $address->id);
        $city = City::getCityByNameAndIdState($address->city, $address->id_state);

        $cityAddress->id_city = $city->id;
        $cityAddress->id_address = $address->id;
        $cityAddress->save();
    }

    // TODO: Use grids

    /**
     * @see /src/Core/Grid/Definition/Factory/AbstractGridDefinitionFactory.php#L93
     * @param GridDefinition $params['definition']
     */
    public function hookActionAddressGridDefinitionModifier($params)
    {
        Logger::AddLog('actionAddressGridDefinitionModifier: '.print_r($params, true));
        /** @var GridDefinitionInterface $definition */
        $definition = $params['definition'];
    }

    /**
     * @see /src/Core/Grid/Data/Factory/DoctrineGridDataFactory.php#L90
     * @param array [
     *   @var 'search_query_builder'
     *   @var 'count_query_builder'
     *   @var SearchCriteriaInterface $search_criteria
     * ]
     */
    public function hookActionAddressGridQueryBuilderModifier($params)
    {
        Logger::AddLog('actionAddressGridQueryBuilderModifier: '.print_r($params, true));
        /** @var QueryBuilder $searchQueryBuilder */
        $searchQueryBuilder = $params['search_query_builder'];

        /** @var CustomerFilters $searchCriteria */
        $searchCriteria = $params['search_criteria'];
    }

    /**
     * @see /src/Core/Form/IdentifiableObject/Builder/FormBuilder.php#L122
     * @param array [
     *   @var 'form_builder'
     *   @var &'data'
     *   @var id $search_criteria
     * ]
     */
    public function hookActionAddressFormBuilderModifier($params)
    {
        Logger::AddLog('actionAddressFormBuilderModifier: '.print_r($params, true));
        /** @var FormBuilderInterface $formBuilder */
        $formBuilder = $params['form_builder'];
    }

    /**
     * @see /src/Core/Form/IdentifiableObject/Handler/FormHandler.php#L170
     * @param array [
     *   @var 'id'
     *   @var &'form_data'
     * ]
     */
    public function hookActionAfterCreateAddressFormHandler($params)
    {
        Logger::AddLog('actionAfterCreateAddressFormHandler: '.print_r($params, true));
    }

    /**
     * @see /src/Core/Form/IdentifiableObject/Handler/FormHandler.php#L145
     * @param array [
     *   @var 'id'
     *   @var &'form_data'
     * ]
     */
    public function hookActionAfterUpdateAddressFormHandler($params)
    {
        Logger::AddLog('actionAfterUpdateAddressFormHandler: '.print_r($params, true));
    }
}
