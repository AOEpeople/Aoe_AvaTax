<?php

class Aoe_AvaTax_Helper_AddressValidator
{
    public function validate(Mage_Sales_Model_Quote_Address $address, $force = false)
    {
        // Address validation is currently only valid for US and CA addresses
        if ($address->getCountryId() !== 'US' && $address->getCountryId() !== 'CA') {
            return;
        }

        /** @var Aoe_AvaTax_Helper_Data $helper */
        $helper = Mage::helper('Aoe_AvaTax/Data');

        // If the module is not active for this address, exit early
        if (!$helper->isAddressValidationActive($address->getQuote()->getStoreId())) {
            return;
        }

        // If we are not forcing validation, then check if the address already has errors so we can exit early
        if (!(bool)$force) {
            /**
             * Get the current errors for the address
             *
             * Since this is protected the listener has to cheat to get access.
             *
             * @see Mage_Customer_Model_Address_Abstract::_getErrors
             */
            $method = new ReflectionMethod($address, '_getErrors');
            $method->setAccessible(true);

            /** @var string[] $errors */
            $errors = $method->invoke($address);

            // If the address validation already has errors, then skip the AvaTax validation
            if (count($errors)) {
                return;
            }
        }

        $store = $address->getQuote()->getStore();

        $result = $helper->getApi($store)->callValidateQuoteAddress($address);
        if ($result['ResultCode'] === 'Success') {
            if ($helper->isAddressNormalizationActive($store)) {
                $validAddress = reset($result['ValidAddresses']);
                if (is_array($validAddress)) {
                    // Define which fields are track regarding normalization notifications
                    $trackedFields = array('street', 'city', 'region_id', 'postcode', 'country_id');

                    // Generate the pre-normalization values
                    $startData = array();
                    foreach ($trackedFields as $field) {
                        $startData[$field] = $address->getData($field);
                    }

                    // Find the region model by country and region code
                    /** @var Mage_Directory_Model_Region $regionModel */
                    $regionModel = Mage::getModel('directory/region')->loadByCode($validAddress['Region'], $validAddress['Country']);

                    // Update the address with the received values - These may be identical
                    $address->setStreetFull(
                        array(
                            $validAddress['Line1'],
                            $validAddress['Line2'],
                            $validAddress['Line3'],
                        )
                    );
                    $address->setCity($validAddress['City']);
                    $address->setRegionId($regionModel->getId());
                    $address->setPostcode($validAddress['PostalCode']);
                    $address->setCountryId($validAddress['Country']);

                    // Check if any of the tracked fields were changed
                    $normalized = false;
                    foreach ($trackedFields as $field) {
                        if ($startData[$field] !== $address->getData($field)) {
                            $normalized = true;
                            break;
                        }
                    }

                    // Store the was_normalized flag on the address
                    if ($normalized) {
                        // We only set the flag if it is true to prevent loss of a previous setting of the value
                        // This happens if validate is called 2+ times on an address
                        $address->setData('was_normalized', true);
                    }
                }
            }
        } else {
            // Define which errored fields we are allowing the customer to see
            $trackedErrors = array('Line1', 'Line2', 'Line3', 'City', 'Region', 'PostalCode', 'Country');

            // Loop the messages looking for error to show the customer, adding those to the address object
            $foundTrackedError = false;
            foreach ($result['Messages'] as $message) {
                if ((strpos($message['RefersTo'], 'Address.') === 0) && in_array(substr($message['RefersTo'], 8), $trackedErrors)) {
                    $address->addError($message['Summary']);
                    $foundTrackedError = true;
                }
            }

            // If we didn't find an errors that could be reported directly to the customer, then add a generic message.
            if (!$foundTrackedError) {
                $address->addError($helper->__('Could not validate address.'));
            }
        }
    }
}
