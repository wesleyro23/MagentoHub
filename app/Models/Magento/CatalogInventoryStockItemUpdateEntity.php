<?php
namespace App\Models\Magento;
/**
 * Created by PhpStorm.
 * User: woliveira
 * Date: 19/09/2016
 * Time: 10:43
 */

class CatalogInventoryStockItemUpdateEntity
{
    private $qty;
    private $is_in_stock;
    private $manage_stock;
    private $use_config_manage_stock;
    private $min_qty;
    private $use_config_min_qty;
    private $min_sale_qty;
    private $use_config_min_sale_qty;
    private $max_sale_qty;
    private $use_config_max_sale_qty;
    private $is_qty_decimal;
    private $backorders;
    private $use_config_backorders;
    private $notify_stock_qty;
    private $use_config_notify_stock_qty;

    /**
     * @return mixed
     */
    public function getQty()
    {
        return $this->qty;
    }

    /**
     * @param mixed $qty
     */
    public function setQty($qty)
    {
        $this->qty = $qty;
    }

    /**
     * @return mixed
     */
    public function getIsInStock()
    {
        return $this->is_in_stock;
    }

    /**
     * @param mixed $is_in_stock
     */
    public function setIsInStock($is_in_stock)
    {
        $this->is_in_stock = $is_in_stock;
    }

    /**
     * @return mixed
     */
    public function getManageStock()
    {
        return $this->manage_stock;
    }

    /**
     * @param mixed $manage_stock
     */
    public function setManageStock($manage_stock)
    {
        $this->manage_stock = $manage_stock;
    }

    /**
     * @return mixed
     */
    public function getUseConfigManageStock()
    {
        return $this->use_config_manage_stock;
    }

    /**
     * @param mixed $use_config_manage_stock
     */
    public function setUseConfigManageStock($use_config_manage_stock)
    {
        $this->use_config_manage_stock = $use_config_manage_stock;
    }

    /**
     * @return mixed
     */
    public function getMinQty()
    {
        return $this->min_qty;
    }

    /**
     * @param mixed $min_qty
     */
    public function setMinQty($min_qty)
    {
        $this->min_qty = $min_qty;
    }

    /**
     * @return mixed
     */
    public function getUseConfigMinQty()
    {
        return $this->use_config_min_qty;
    }

    /**
     * @param mixed $use_config_min_qty
     */
    public function setUseConfigMinQty($use_config_min_qty)
    {
        $this->use_config_min_qty = $use_config_min_qty;
    }

    /**
     * @return mixed
     */
    public function getMinSaleQty()
    {
        return $this->min_sale_qty;
    }

    /**
     * @param mixed $min_sale_qty
     */
    public function setMinSaleQty($min_sale_qty)
    {
        $this->min_sale_qty = $min_sale_qty;
    }

    /**
     * @return mixed
     */
    public function getUseConfigMinSaleQty()
    {
        return $this->use_config_min_sale_qty;
    }

    /**
     * @param mixed $use_config_min_sale_qty
     */
    public function setUseConfigMinSaleQty($use_config_min_sale_qty)
    {
        $this->use_config_min_sale_qty = $use_config_min_sale_qty;
    }

    /**
     * @return mixed
     */
    public function getMaxSaleQty()
    {
        return $this->max_sale_qty;
    }

    /**
     * @param mixed $max_sale_qty
     */
    public function setMaxSaleQty($max_sale_qty)
    {
        $this->max_sale_qty = $max_sale_qty;
    }

    /**
     * @return mixed
     */
    public function getUseConfigMaxSaleQty()
    {
        return $this->use_config_max_sale_qty;
    }

    /**
     * @param mixed $use_config_max_sale_qty
     */
    public function setUseConfigMaxSaleQty($use_config_max_sale_qty)
    {
        $this->use_config_max_sale_qty = $use_config_max_sale_qty;
    }

    /**
     * @return mixed
     */
    public function getIsQtyDecimal()
    {
        return $this->is_qty_decimal;
    }

    /**
     * @param mixed $is_qty_decimal
     */
    public function setIsQtyDecimal($is_qty_decimal)
    {
        $this->is_qty_decimal = $is_qty_decimal;
    }

    /**
     * @return mixed
     */
    public function getBackorders()
    {
        return $this->backorders;
    }

    /**
     * @param mixed $backorders
     */
    public function setBackorders($backorders)
    {
        $this->backorders = $backorders;
    }

    /**
     * @return mixed
     */
    public function getUseConfigBackorders()
    {
        return $this->use_config_backorders;
    }

    /**
     * @param mixed $use_config_backorders
     */
    public function setUseConfigBackorders($use_config_backorders)
    {
        $this->use_config_backorders = $use_config_backorders;
    }

    /**
     * @return mixed
     */
    public function getNotifyStockQty()
    {
        return $this->notify_stock_qty;
    }

    /**
     * @param mixed $notify_stock_qty
     */
    public function setNotifyStockQty($notify_stock_qty)
    {
        $this->notify_stock_qty = $notify_stock_qty;
    }

    /**
     * @return mixed
     */
    public function getUseConfigNotifyStockQty()
    {
        return $this->use_config_notify_stock_qty;
    }

    /**
     * @param mixed $use_config_notify_stock_qty
     */
    public function setUseConfigNotifyStockQty($use_config_notify_stock_qty)
    {
        $this->use_config_notify_stock_qty = $use_config_notify_stock_qty;
    }
        
    
}