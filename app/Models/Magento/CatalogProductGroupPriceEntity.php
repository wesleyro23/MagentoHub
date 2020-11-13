<?php
namespace App\Models\Magento;
/**
 * Created by PhpStorm.
 * User: woliveira
 * Date: 19/09/2016
 * Time: 11:17
 */
class CatalogProductGroupPriceEntity
{
    private $cust_group;
    private $website_id;
    private $price;

    /**
     * @return mixed
     */
    public function getCustGroup()
    {
        return $this->cust_group;
    }

    /**
     * @param mixed $cust_group
     */
    public function setCustGroup($cust_group)
    {
        $this->cust_group = $cust_group;
    }

    /**
     * @return mixed
     */
    public function getWebsiteId()
    {
        return $this->website_id;
    }

    /**
     * @param mixed $website_id
     */
    public function setWebsiteId($website_id)
    {
        $this->website_id = $website_id;
    }

    /**
     * @return mixed
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param mixed $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }
    
    
}