<?php

namespace App\Models\Magento;


class CatalogProductRequestAttributes
{
    private $additional_attributes = array();

    /**
     * @return array
     */
    public function getAdditionalAttributes()
    {
        return $this->additional_attributes;
    }

    /**
     * @param array $additional_attributes
     */
    public function setAdditionalAttributes($additional_attributes)
    {
        $this->additional_attributes = $additional_attributes;
    }
    
    
}
