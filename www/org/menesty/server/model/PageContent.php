<?php

/**
 * User: Menesty
 * Date: 1/20/15
 * Time: 09:52
 */
class PageContent
{
    private $contentKey;
    private $title;
    private $content;

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $content
     */
    public function setContent($content)
    {
        $this->content = $content;
    }

    /**
     * @return mixed
     */
    public function getContentKey()
    {
        return $this->contentKey;
    }

    /**
     * @param mixed $contentKey
     */
    public function setContentKey($contentKey)
    {
        $this->contentKey = $contentKey;
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