# ps_cities

Cities module for PrestaShop 1.7.7.X

## Install

Clone the module:

`git clone https://github.com/jorgevrgs/ps_cities.git`

Run composer:

`composer dump-autoload`

Then upload to your `/modules` path.

Install and configure cities in `International` - `Locations` - `Cities` tab.

## PrestaShop 1.7.5 - 1.7.6

Modify the file `classes/form/CustomerAddressFormatter.php`:

In the line 150 for 1.7.7 it looks like:

https://github.com/PrestaShop/PrestaShop/blob/1.7.7.x/classes/form/CustomerAddressFormatter.php#L150

```php
$additionalAddressFormFields = Hook::exec('additionalCustomerAddressFields', ['fields' => &$format], null, true);
```

In the line 150 for 1.7.6 it looks like:

https://github.com/PrestaShop/PrestaShop/blob/1.7.6.x/classes/form/CustomerAddressFormatter.php#L150

```php
$additionalAddressFormFields = Hook::exec('additionalCustomerAddressFields', array(), null, true);
```

For previous versionS (1.7.5-1.7.6) it's necessary to include the whole block in the line 150:

```php
        //To add the extra fields in address form
        $additionalAddressFormFields = Hook::exec('additionalCustomerAddressFields', ['fields' => &$format], null, true);
        if (is_array($additionalAddressFormFields)) {
            foreach ($additionalAddressFormFields as $moduleName => $additionnalFormFields) {
                if (!is_array($additionnalFormFields)) {
                    continue;
                }

                foreach ($additionnalFormFields as $formField) {
                    $formField->moduleName = $moduleName;
                    $format[$moduleName . '_' . $formField->getName()] = $formField;
                }
            }
        }
```

For older versions include the hooks:

```
'additionalCustomerAddressFields'
'actionValidateCustomerAddressForm'
'actionSubmitCustomerAddressForm'
```
