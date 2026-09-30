<?php

declare(strict_types=1);

namespace Elgentos\ImprovedCustomerAddressValidation\Model\Validator;

use Magento\Customer\Model\Validator\Name as OriginalNameValidator;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Name extends OriginalNameValidator
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
        if (!$this->scopeConfig->isSetFlag('customer/address/enable_name_validation', ScopeInterface::SCOPE_STORE)) {
            return true;
        }

        if (!$this->isValidName($customer->getFirstname())) {
            parent::_addMessages([['firstname' => 'First Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getLastname())) {
            parent::_addMessages([['lastname' => 'Last Name is not valid!']]);
        }

        if (!$this->isValidName($customer->getMiddlename())) {
            parent::_addMessages([['middlename' => 'Middle Name is not valid!']]);
        }

        return count($this->_messages) == 0;
    }

    /**
     * @param $nameValue
     *
     * @return bool
     */
    private function isValidName($nameValue)
    {
        if ($nameValue == null) {
            return true;
        }

        if ($this->scopeConfig->isSetFlag('customer/address/use_builtin_name_regex', ScopeInterface::SCOPE_STORE)) {
            $pattern = '/(?:[\p{L}\p{M}\,\-\_\.\'’`&\s\d]){1,255}+/u';
        } else {
            $pattern = $this->scopeConfig->getValue('customer/address/name_validation_regex', ScopeInterface::SCOPE_STORE);
        }

        return $this->matchesEntireValue($pattern, (string) $nameValue);
    }
}
