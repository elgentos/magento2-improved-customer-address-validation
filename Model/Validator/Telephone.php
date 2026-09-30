<?php

declare(strict_types=1);

namespace Elgentos\ImprovedCustomerAddressValidation\Model\Validator;

use Magento\Customer\Model\Validator\Telephone as OriginalTelephoneValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Telephone extends OriginalTelephoneValidator
{
    use FullValueMatchTrait;

    public function __construct(
        private readonly ScopeConfigInterface $scopeConfig
    ) {}

    /**
     * @param $customer
     *
     * @return bool
     */
    public function isValid($customer)
    {
        if (!$this->scopeConfig->isSetFlag('customer/address/enable_telephone_validation', ScopeInterface::SCOPE_STORE)) {
            return true;
        }

        if (!$this->isValidTelephone($customer->getTelephone())) {
            parent::_addMessages([[
                'telephone' => 'Invalid Phone Number'
            ]]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * @param $telephoneValue
     *
     * @return bool
     */
    private function isValidTelephone($telephoneValue)
    {
        if ($telephoneValue == null) {
            return true;
        }

        if ($this->scopeConfig->isSetFlag('customer/address/use_builtin_telephone_regex', ScopeInterface::SCOPE_STORE)) {
            $pattern = '/(?:[\d\s\+\-\()]{1,20})/u';
        } else {
            $pattern = $this->scopeConfig->getValue('customer/address/telephone_validation_regex', ScopeInterface::SCOPE_STORE);
        }

        return $this->matchesEntireValue($pattern, (string) $telephoneValue);
    }
}
