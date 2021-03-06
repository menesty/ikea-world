<?php

/**
 * User: Menesty
 * Date: 1/2/15
 * Time: 23:01
 */
class Category
{
    private $id;

    private $name;

    private $subCategories;

    private $parentId;

    private $productCount = 0;

    private $ikeaUrl;

    /**
     * @return mixed
     */
    public function getIkeaUrl()
    {
        return $this->ikeaUrl;
    }

    /**
     * @param mixed $ikeaUrl
     */
    public function setIkeaUrl($ikeaUrl)
    {
        $this->ikeaUrl = $ikeaUrl;
    }


    /**
     * @return mixed
     */
    public function getProductCount()
    {
        return (int)$this->productCount;
    }

    /**
     * @param mixed $productCount
     */
    public function setProductCount($productCount)
    {
        $this->productCount = $productCount;
    }


    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getSubCategories()
    {
        return $this->subCategories;
    }

    /**
     * @param mixed $subCategories
     */
    public function setSubCategories($subCategories)
    {
        $this->subCategories = $subCategories;
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
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param mixed $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    public function hasCategories()
    {
        return sizeof($this->subCategories) != 0;
    }


}