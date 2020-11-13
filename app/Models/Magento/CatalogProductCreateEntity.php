<?php
namespace App\Models\Magento;
/**
 * Created by PhpStorm.
 * User: woliveira
 * Date: 19/09/2016
 * Time: 10:18
 */
class CatalogProductCreateEntity
{
    //Atributos
    private $categories = array();
    private $websites = array();
    private $name;
    private $description;
    private $short_description;
    private $weight;    
    private $status;
    private $url_key;
    private $url_path;
    private $visibility;
    private $category_ids = array();
    private $website_ids = array();
    private $has_options;
    private $gift_message_available;
    private $price;
    private $special_price;
    private $special_from_date;
    private $special_to_date;
    private $tax_class_id;
    /*ws.signashop.magento.CatalogProductTierPriceEntity[] tier_price;*/
    private $meta_title;
    private $meta_keyword;
    private $meta_description;
    private $custom_design;
    private $custom_layout_update;
    private $options_container;
    private $additional_attributes = CatalogProductAdditionalAttributesEntity::class;
    private $stock_data = CatalogInventoryStockItemUpdateEntity::class;
    private $associated_skus = array();
    private $configurable_attributes = array();
    private $price_changes = AssociativeEntity::class;
    private $group_price = CatalogProductGroupPriceEntity::class;


    /**
     * @return array
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param array $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return array
     */
    public function getWebsites()
    {
        return $this->websites;
    }

    /**
     * @param array $websites
     */
    public function setWebsites($websites)
    {
        $this->websites = $websites;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * @param mixed $short_description
     */
    public function setShortDescription($short_description)
    {
        $this->short_description = $short_description;
    }

    /**
     * @return mixed
     */
    public function getWeight()
    {
        return $this->weight;
    }

    /**
     * @param mixed $weight
     */
    public function setWeight($weight)
    {
        $this->weight = $weight;
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param mixed $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return mixed
     */
    public function getUrlKey()
    {
        return $this->url_key;
    }

    /**
     * @param mixed $url_key
     */
    public function setUrlKey($url_key)
    {
        $this->url_key = $url_key;
    }

    /**
     * @return mixed
     */
    public function getUrlPath()
    {
        return $this->url_path;
    }

    /**
     * @param mixed $url_path
     */
    public function setUrlPath($url_path)
    {
        $this->url_path = $url_path;
    }

    /**
     * @return mixed
     */
    public function getVisibility()
    {
        return $this->visibility;
    }

    /**
     * @param mixed $visibility
     */
    public function setVisibility($visibility)
    {
        $this->visibility = $visibility;
    }

    /**
     * @return array
     */
    public function getCategoryIds()
    {
        return $this->category_ids;
    }

    /**
     * @param array $category_ids
     */
    public function setCategoryIds($category_ids)
    {
        $this->category_ids = $category_ids;
    }

    /**
     * @return array
     */
    public function getWebsiteIds()
    {
        return $this->website_ids;
    }

    /**
     * @param array $website_ids
     */
    public function setWebsiteIds($website_ids)
    {
        $this->website_ids = $website_ids;
    }

    /**
     * @return mixed
     */
    public function getHasOptions()
    {
        return $this->has_options;
    }

    /**
     * @param mixed $has_options
     */
    public function setHasOptions($has_options)
    {
        $this->has_options = $has_options;
    }

    /**
     * @return mixed
     */
    public function getGiftMessageAvailable()
    {
        return $this->gift_message_available;
    }

    /**
     * @param mixed $gift_message_available
     */
    public function setGiftMessageAvailable($gift_message_available)
    {
        $this->gift_message_available = $gift_message_available;
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

    /**
     * @return mixed
     */
    public function getSpecialPrice()
    {
        return $this->special_price;
    }

    /**
     * @param mixed $special_price
     */
    public function setSpecialPrice($special_price)
    {
        $this->special_price = $special_price;
    }

    /**
     * @return mixed
     */
    public function getSpecialFromDate()
    {
        return $this->special_from_date;
    }

    /**
     * @param mixed $special_from_date
     */
    public function setSpecialFromDate($special_from_date)
    {
        $this->special_from_date = $special_from_date;
    }

    /**
     * @return mixed
     */
    public function getSpecialToDate()
    {
        return $this->special_to_date;
    }

    /**
     * @param mixed $special_to_date
     */
    public function setSpecialToDate($special_to_date)
    {
        $this->special_to_date = $special_to_date;
    }

    /**
     * @return mixed
     */
    public function getTaxClassId()
    {
        return $this->tax_class_id;
    }

    /**
     * @param mixed $tax_class_id
     */
    public function setTaxClassId($tax_class_id)
    {
        $this->tax_class_id = $tax_class_id;
    }

    /**
     * @return mixed
     */
    public function getMetaTitle()
    {
        return $this->meta_title;
    }

    /**
     * @param mixed $meta_title
     */
    public function setMetaTitle($meta_title)
    {
        $this->meta_title = $meta_title;
    }

    /**
     * @return mixed
     */
    public function getMetaKeyword()
    {
        return $this->meta_keyword;
    }

    /**
     * @param mixed $meta_keyword
     */
    public function setMetaKeyword($meta_keyword)
    {
        $this->meta_keyword = $meta_keyword;
    }

    /**
     * @return mixed
     */
    public function getMetaDescription()
    {
        return $this->meta_description;
    }

    /**
     * @param mixed $meta_description
     */
    public function setMetaDescription($meta_description)
    {
        $this->meta_description = $meta_description;
    }

    /**
     * @return mixed
     */
    public function getCustomDesign()
    {
        return $this->custom_design;
    }

    /**
     * @param mixed $custom_design
     */
    public function setCustomDesign($custom_design)
    {
        $this->custom_design = $custom_design;
    }

    /**
     * @return mixed
     */
    public function getCustomLayoutUpdate()
    {
        return $this->custom_layout_update;
    }

    /**
     * @param mixed $custom_layout_update
     */
    public function setCustomLayoutUpdate($custom_layout_update)
    {
        $this->custom_layout_update = $custom_layout_update;
    }

    /**
     * @return mixed
     */
    public function getOptionsContainer()
    {
        return $this->options_container;
    }

    /**
     * @param mixed $options_container
     */
    public function setOptionsContainer($options_container)
    {
        $this->options_container = $options_container;
    }

    /**
     * @return mixed
     */
    public function getAdditionalAttributes()
    {
        return $this->additional_attributes;
    }

    /**
     * @param mixed $additional_attributes
     */
    public function setAdditionalAttributes($additional_attributes)
    {
        $this->additional_attributes = $additional_attributes;
    }

    /**
     * @return mixed
     */
    public function getStockData()
    {
        return $this->stock_data;
    }

    /**
     * @param mixed $stock_data
     */
    public function setStockData($stock_data)
    {
        $this->stock_data = $stock_data;
    }

    /**
     * @return array
     */
    public function getAssociatedSkus()
    {
        return $this->associated_skus;
    }

    /**
     * @param array $associated_skus
     */
    public function setAssociatedSkus($associated_skus)
    {
        $this->associated_skus = $associated_skus;
    }

    /**
     * @return array
     */
    public function getConfigurableAttributes()
    {
        return $this->configurable_attributes;
    }

    /**
     * @param array $configurable_attributes
     */
    public function setConfigurableAttributes($configurable_attributes)
    {
        $this->configurable_attributes = $configurable_attributes;
    }

    /**
     * @return mixed
     */
    public function getPriceChanges()
    {
        return $this->price_changes;
    }

    /**
     * @param mixed $price_changes
     */
    public function setPriceChanges($price_changes)
    {
        $this->price_changes = $price_changes;
    }

    /**
     * @return mixed
     */
    public function getGroupPrice()
    {
        return $this->group_price;
    }

    /**
     * @param mixed $group_price
     */
    public function setGroupPrice($group_price)
    {
        $this->group_price = $group_price;
    }

}