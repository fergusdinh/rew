<?php
namespace Lof\RewardPoints\Api\Data;

interface RedeemSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{


    /**
     * Get Redeem list.
     * @return \Lof\RewardPoints\Api\Data\RedeemInterface[]
     */
    public function getItems();

    /**
     * Set code_id list.
     * @param \Lof\RewardPoints\Api\Data\RedeemInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
