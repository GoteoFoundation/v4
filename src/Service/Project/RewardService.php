<?php

namespace App\Service\Project;

use App\Entity\Project\Reward;
use App\Entity\Project\RewardClaim;

class RewardService
{
    /**
     * Processes a RewardClaim for the set Reward
     * 
     * @param Reward $reward The Reward being claimed
     * 
     * @return RewardClaim The processed RewardClaim with updated Reward
     */
    public function processClaim(RewardClaim $claim): RewardClaim
    {
        $reward = $claim->getReward();

        if (!$reward->hasUnits()) {
            return $claim;
        }

        $available = $reward->getUnitsAvailable();

        if ($available < 1) {
            throw new \Exception("The claimed Reward has no units available");
        }

        $reward->addClaim($claim);
        $reward->setUnitsAvailable($available - 1);

        return $claim->setReward($reward);
    }
}
