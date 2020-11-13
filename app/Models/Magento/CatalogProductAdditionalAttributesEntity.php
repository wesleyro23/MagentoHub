<?php
namespace App\Models\Magento;
/**
 * Created by PhpStorm.
 * User: woliveira
 * Date: 19/09/2016
 * Time: 11:07
 */
class CatalogProductAdditionalAttributesEntity
{
    private $single_data = array();

    /**
     * @return array
     */
    public function getSingleData()
    {
        return $this->single_data;
    }

    /**
     * @param array $single_data
     */
    public function setSingleData($single_data)
    {
        $this->single_data[] = $single_data;
    }


}