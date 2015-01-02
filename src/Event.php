<?php

namespace Burrow;

class Event
{
    /**
     * @var string
     */
    protected $category;

    /**
     * @var mixed
     */
    protected $data;

    /**
     * @param string       $category
     * @param mixed        $data
     */
    function __construct($category, $data)
    {
        $this->category = $category;
        $this->data     = $data;
    }

    /**
     * @return string
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @return mixed
     */
    public function getData()
    {
        return $this->data;
    }
}
