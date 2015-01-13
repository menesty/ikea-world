<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "ProductImage.php");

/**
 * User: Menesty
 * Date: 12/23/14
 * Time: 09:38
 */
class Product
{
    private $id;
    private $artNumber;
    private $title;
    private $shortDescription;
    private $designer;
    private $size;
    private $packing;
    private $description;
    private $instruction;
    private $price = 0;
    private $published;
    private $available;
    private $images = null;

    /**
     * @return mixed
     */
    public function getArtNumber()
    {
        return $this->artNumber;
    }

    /**
     * @param mixed $artNumber
     */
    public function setArtNumber($artNumber)
    {
        $this->artNumber = $artNumber;
    }

    /**
     * @return mixed
     */
    public function getAvailable()
    {
        return $this->available;
    }

    /**
     * @param mixed $available
     */
    public function setAvailable($available)
    {
        $this->available = $available;
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
    public function getDesigner()
    {
        return $this->designer;
    }

    /**
     * @param mixed $designer
     */
    public function setDesigner($designer)
    {
        $this->designer = $designer;
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
    public function getInstruction()
    {
        return $this->instruction;
    }

    /**
     * @param mixed $instruction
     */
    public function setInstruction($instruction)
    {
        $this->instruction = $instruction;
    }

    /**
     * @return mixed
     */
    public function getPacking()
    {
        return $this->packing;
    }

    /**
     * @param mixed $packing
     */
    public function setPacking($packing)
    {
        $this->packing = $packing;
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
    public function getPublished()
    {
        return $this->published;
    }

    /**
     * @param mixed $published
     */
    public function setPublished($published)
    {
        $this->published = $published;
    }

    /**
     * @return mixed
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param mixed $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * @return mixed
     */
    public function getSize()
    {
        return $this->size;
    }

    /**
     * @param mixed $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    public function getImages()
    {
        if (is_null($this->images)) {
            $this->images = array();

            $path = Utils::getProductImagePath($this->artNumber) . "normal" . DIRECTORY_SEPARATOR;


            $index = 0;
            foreach (new DirectoryIterator($path) as $fileInfo) {
                if ($fileInfo->isDot()) continue;

                $this->images[] = new ProductImage($this->artNumber, $index);
                $index++;
            }
        }

        return $this->images;
    }

    public function getPreparedArtNumber()
    {
        $length = strlen($this->artNumber);
        return substr($this->artNumber, $length - 8, 3) . "." . substr($this->artNumber, $length - 5, 3) .
        "." . substr($this->artNumber, $length - 2, 2);
    }

    public function getSellPrice()
    {
        return $this->price;
    }

} 