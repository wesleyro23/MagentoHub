<?php
namespace App\Models\Magento;
/**
 * Created by PhpStorm.
 * User: woliveira
 * Date: 19/09/2016
 * Time: 11:00
 */
class AssociativeEntity
{
    private $key;
    private $value;

    /**
     * @return mixed
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param mixed $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param mixed $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }
    
    
}