<?php

namespace SiteOrigin\PageCache\Condition;

use Illuminate\Filesystem\FilesystemAdapter;

abstract class Condition
{
    protected string $condition;

    protected $args;

    /**
     * @var FilesystemAdapter|null
     */
    protected ?FilesystemAdapter $filesystem;

    public function __construct(string $condition, array $args=[])
    {
        $this->condition = static::filterCondition($condition);
        $this->args = $args;
    }

    /**
     * @param $filesystem
     * @return \SiteOrigin\PageCache\Condition\Condition
     */
    public function setFilesystem(FilesystemAdapter $filesystem): Condition
    {
        $this->filesystem = $filesystem;
        return $this;
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
    public static function fromStringArray(array $conditions, array $args=[]): array
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
    protected static function filterCondition($condition): string
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

    /**
     * Perform the filter action on the URL/file combination.
     *
     * @param string $url
     * @param string $file
     * @return mixed
     */
    abstract function filter(string $url, string $file);

    public function __invoke(string $url, string $file)
    {
        return $this->filter($url, $file);
    }
}