<?php

declare(strict_types=1);

namespace Elgentos\ImprovedCustomerAddressValidation\Model\Validator;

use Magento\Customer\Model\Validator\Street as OriginalStreetValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Street extends OriginalStreetValidator
{
    use FullValueMatchTrait;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * Validate street fields.
     *
     * @param $customer
     *
     * @return bool
     */
    public function isValid($customer)
    {
        if (!$this->scopeConfig->isSetFlag('customer/address/enable_street_validation', ScopeInterface::SCOPE_STORE)) {
            return true;
        }

        foreach ($customer->getStreet() as $street) {
            if (!$this->isValidStreet($street)) {
                parent::_addMessages([[
                    'street' => "Invalid Street Address"
                ]]);
            }
        }

        return count($this->_messages) == 0;
    }

    /**
     * @param $streetValue
     * @param $pattern
     *
     * @return bool
     */
    private function isValidStreet($streetValue)
    {
        if ($streetValue == null) {
            return true;
        }

        if ($this->scopeConfig->isSetFlag('customer/address/use_builtin_street_regex', ScopeInterface::SCOPE_STORE)) {
            // The unescaped "[" and the "]" that follows it closed the character class early, so
            // the pattern never matched any address. Core has the same typo, but there it is
            // harmless: Magento returns true when nothing matches. Here a non-match means
            // "invalid", so every address was rejected once street validation was switched on.
            $pattern = "/(?:[\p{L}\p{M}\"\[\],\-.'’`&\s\d]){1,255}+/u";
        } else {
            $pattern = $this->scopeConfig->getValue('customer/address/street_validation_regex',
                ScopeInterface::SCOPE_STORE);
        }

        return $this->matchesEntireValue($pattern, (string) $streetValue);
    }
}
