<?php

namespace Lof\RewardPointsBehavior\Api;

interface EarnPointsBehaviorsInterface
{
	/**
     * Returns information for used credit in a specified cart.
     *
     * @return string
     */
	public function getEarnPointsByBehaviors();
}