<?php

/**
 * User: Menesty
 * Date: 1/4/15
 * Time: 10:55
 */
class Paging implements Iterator
{
    private $activePage;

    private $pageCount;

    private $position;

    private $showPages = 6;

    private $startPage = 1;

    private $endPage = 1;

    public function __construct($pageCount, $activePage = 0)
    {
        $this->pageCount = $pageCount;
        $this->activePage = $activePage;

        if ($this->activePage == 0 || $this->activePage > $this->pageCount) {
            $this->startPage = 1;
        } else {
            $half = round($this->showPages / 2);
            $this->startPage = $this->activePage - $half < 1 ? 1 : $this->activePage - $half;
        }

        $this->endPage = ($this->startPage + $this->showPages >= $pageCount ? $pageCount : ($this->startPage + $this->showPages));
        if ($this->endPage == 0) {
            $this->endPage = 1;
        }

    }


    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
        return $this->position;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->position <= $this->endPage;

    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->position = $this->startPage;
    }

    public function isFirst()
    {
        return $this->activePage == 1;
    }

    public function isLast()
    {
        return $this->activePage == $this->pageCount;
    }

    public function isActive()
    {
        return $this->activePage == $this->position;
    }

    public function canNext()
    {
        return $this->activePage < $this->pageCount;
    }

    public function canPrev()
    {
        return $this->activePage > 1;
    }

    public function getPageCount()
    {
        return $this->pageCount;
    }

    public function getActive()
    {
        return $this->activePage;
    }
}