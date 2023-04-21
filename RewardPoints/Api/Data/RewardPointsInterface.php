<?php


namespace Lof\RewardPoints\Api\Data;

interface RewardPointsInterface
{

    const RULE_ID = 'rule_id';
    const NAME = 'name';
    const DESCRIPTION = 'description';
    const IS_ACTIVE = 'is_active';
    const ACTIVE_FROM = 'active_from';
    const ACTIVE_TO = 'active_to';
    const TYPE = 'type';
    const ACTION = 'action';
    const SPEND_POINTS = 'spend_points';
    const MONETARY_STEP = 'monetary_step';
    const SPEND_MIN_POINTS = 'spend_min_points';
    const SPEND_MAX_POINTS = 'spend_max_points';
    const SORT_ORDER = 'sort_order';
    const IS_STOP_PROCESSING = 'is_stop_processing';
    const CONDITIONS_SERIALIZED = 'conditions_serialized';
    const ACTIONS_SERIALIZED = 'actions_serialized';
    const QTY_STEP = 'qty_step';
    const PERCENTAGE_MAX_POINTS = 'percentage_max_points';

    /**
     * Get rule_id
     * @return int
     */
    public function getRuleId();

    /**
     * Set rule_id
     * @param int $ruleId
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setRuleId($ruleId);
    /**
     * Get name
     * @return string
     */
    public function getName();

    /**
     * Set name
     * @param string $name
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setName($name);

    /**
     * Get description
     * @return string|null
     */
    public function getDescription();

    /**
     * Set description
     * @param string $description
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setDescription($description);

    /**
     * Get is_active
     * @return int
     */
    public function getIsActive();

    /**
     * Set is_active
     * @param int $isActive
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setIsActive($isActive);

    /**
     * Get active_from
     * @return string|null
     */
    public function getActiveFrom();

    /**
     * Set active_from
     * @param string $activeFrom
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setActiveFrom($activeFrom);

    /**
     * Get active_to
     * @return string|null
     */
    public function getActiveTo();

    /**
     * Set active_to
     * @param string $activeTo
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setActiveTo($activeTo);

    /**
     * Get type
     * @return string
     */
    public function getType();

    /**
     * Set type
     * @param string $type
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setType($type);

    /**
     * Get action
     * @return string
     */
    public function getAction();

    /**
     * Set action
     * @param string $action
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setAction($action);

    /**
     * Get spend_points
     * @return float|null
     */
    public function getSpendPoints();

    /**
     * Set spend_points
     * @param float $spendPoints
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setSpendPoints($spendPoints);

    /**
     * Get monetary_step
     * @return float|null
     */
    public function getMonetaryStep();

    /**
     * Set monetary_step
     * @param float $monetaryStep
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setMonetaryStep($monetaryStep);

    /**
     * Get spend_min_points
     * @return float|null
     */
    public function getSpendMinPoints();

    /**
     * Set spend_min_points
     * @param float $spendMinPoints
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setSpendMinPoints($spendMinPoints);

    /**
     * Get spend_max_points
     * @return float|null
     */
    public function getSpendMaxPoints();

    /**
     * Set spend_max_points
     * @param float $spendMaxPoints
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setSpendMaxPoints($spendMaxPoints);

    /**
     * Get sort_order
     * @return int|null
     */
    public function getSortOrder();

    /**
     * Set sort_order
     * @param int $sortOrder
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setSortOrder($sortOrder);

    /**
     * Get is_stop_processing
     * @return int|null
     */
    public function getIsStopProcessing();

    /**
     * Set is_stop_processing
     * @param int $isStopProcessing
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setIsStopProcessing($isStopProcessing);

    /**
     * Get conditions_serialized
     * @return string|null
     */
    public function getConditionsSerialized();

    /**
     * Set conditions_serialized
     * @param string $conditionsSerialized
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setConditionsSerialized($conditionsSerialized);

    /**
     * Get actions_serialized
     * @return string|null
     */
    public function getActionsSerialized();

    /**
     * Set actions_serialized
     * @param string $actionsSerialized
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setActionsSerialized($actionsSerialized);

    /**
     * Get qty_step
     * @return string|null
     */
    public function getQtyStep();

    /**
     * Set qty_step
     * @param string $qtyStep
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setQtyStep($qtyStep);
    /**
     * Get percentage_max_points
     * @return float|null
     */
    public function getPercentageMaxPoints();

    /**
     * Set percentage_max_points
     * @param float $percentageMaxPoints
     * @return \Lof\RewardPoints\Api\Data\RewardPointsInterface
     */
    public function setPercentageMaxPoints($percentageMaxPoints);
}
