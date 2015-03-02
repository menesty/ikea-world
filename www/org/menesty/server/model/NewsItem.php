<?php

/**
 * User: Menesty
 * Date: 2/17/15
 * Time: 22:40
 */
class NewsItem
{
    private $id;
    private $title;
    private $shortDescription;
    private $description;
    private $published;
    private $publishedDate;

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
    public function getPublishedDate($format = false)
    {
        if ($format) {
            $value = trim($this->publishedDate);

            if ($value != "") {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

                return $date? $date->format('d/m/Y H:i') : null;
            }
        }

        return $this->publishedDate;
    }

    /**
     * @param mixed $publishedDate
     */
    public function setPublishedDate($publishedDate)
    {
        $this->publishedDate = $publishedDate;
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

} 