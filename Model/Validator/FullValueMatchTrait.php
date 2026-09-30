<?php

declare(strict_types=1);

namespace Elgentos\ImprovedCustomerAddressValidation\Model\Validator;

/**
 * Shared matching logic for the address validators.
 *
 * Magento core anchors its address validation by checking that the pattern matched the entire
 * value (see Magento\Customer\Model\Validator\Telephone::isValidTelephone()). Without that check
 * a pattern only has to match somewhere in the value, which makes the validation close to
 * meaningless: "user@example.com 1234" passes the built-in telephone pattern because it
 * contains "1234".
 */
trait FullValueMatchTrait
{
    /**
     * Check that $pattern matches the complete $value, not just a part of it.
     *
     * An empty or missing pattern means "not configured" and never rejects a value: that keeps a
     * half-finished configuration from blocking every address.
     */
    private function matchesEntireValue(?string $pattern, string $value): bool
    {
        if ($pattern === null || trim($pattern) === '') {
            return true;
        }

        if (preg_match($pattern, $value, $matches) !== 1) {
            return false;
        }

        return $matches[0] === $value;
    }
}
