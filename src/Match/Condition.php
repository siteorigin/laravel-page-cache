<?php

abstract class Condition
{
    protected string $condition;

    public function __construct(string $condition)
    {
        $this->condition = $condition;
    }

    /**
     * Create a new instance of this condition from a string.
     *
     * @param string $condition
     * @return static
     */
    public static function fromString(string $condition)
    {
        return new static($condition);
    }

    /**
     * Check that a given URL is valid against this condition.
     *
     * @param $url
     * @return mixed
     */
    abstract protected function check($url): bool;

    public function __invoke($url)
    {
        return $this->check($url);
    }
}