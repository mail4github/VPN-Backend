<?php

declare(strict_types=1);

namespace App\Security;

use Scheb\TwoFactorBundle\Security\TwoFactor\AuthenticationContextInterface;
use Scheb\TwoFactorBundle\Security\TwoFactor\Condition\TwoFactorConditionInterface;

class TwoFactorCondition implements TwoFactorConditionInterface
{
    public function shouldPerformTwoFactorAuthentication(AuthenticationContextInterface $context): bool
    {
        return \in_array('IS_AUTHENTICATED_2FA_IN_PROGRESS', $context->getToken()->getRoleNames());
    }
}
