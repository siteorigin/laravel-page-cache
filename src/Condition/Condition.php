<?php

namespace SiteOrigin\PageCache\Condition;

abstract class Condition
{
    protected string $condition;

    protected $args;

    public function __construct(string $condition, array $args=[])
    {
        $this->condition = static::filterCondition($condition);
        $this->condition = $condition;
        $this->args = $args;
    }

    /**
     * Create a new instance of this condition from a string.
     *
     * @param string $condition
     * @param array $args
     * @return Condition
     */
    public static function fromString(string $condition, array $args=[])
    {
        if(is_null($condition)) return null;
        return new static($condition, $args);
    }

    /**
     * Create conditions from an array of strings.
     *
     * @param array $conditions
     * @param array $args
     * @return Condition[]
     */
    public static function fromStringArray(array $conditions, array $args=[])
    {
        if(is_null($conditions)) return [];
        return array_map(fn($condition) => new static($condition, $args), $conditions);
    }

    /**
     * Filter the condition before creating the object.
     *
     * @param $condition
     * @return mixed
     */
    protected static function filterCondition($condition)
    {
        return $condition;
    }

    /**
     * Convert the condition to a string for array_unique.
     *
     * @return string
     */
    public function __toString()
    {
        return get_class($this) . '::' . $this->condition . '::' . json_encode($this->args);
    }
}