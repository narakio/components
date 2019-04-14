<?php namespace Naraki\Core\Support\Frontend\Jsonld;

use Illuminate\Support\Str;

class Schema
{
    /**
     * @var string
     */
    protected $type;
    /**
     * @var array
     */
    protected $properties = [];
    /**
     * @var \ReflectionClass
     */
    protected $reflection;

    /**
     *
     * @param array $attributes
     * @param bool $root
     */
    public function __construct(array $attributes, $root = false)
    {
        $path = explode('\\', get_class($this));
        $this->type = end($path);
        try {
            $this->reflection = new \ReflectionClass($this);
        } catch (\ReflectionException $e) {
        }

        $this->fill($attributes, $root);
    }

    /**
     * @param array $attributes
     * @param bool $root
     */
    public function fill(array $attributes, $root)
    {
        if ($root) {
            $this->properties['@context'] = 'http://schema.org';
        }
        $this->properties['@type'] = $this->type;
        if (isset($attributes['url'])) {
            $this->properties['@id'] = sprintf('%s#%s', $attributes['url'], strtolower($this->type));
        }

        foreach ($attributes as $attribute => $value) {
            if (isset($value) && !empty($value)) {
                if (strpos($attribute, '@') !== false) {
                    $methodClass = explode('@', $attribute);
                    if ($methodClass[1] !== 'id') {
                        $this->properties[$methodClass[0]] = $this->{'set' . ucfirst($methodClass[0])}($value,
                            $methodClass[1]);
                    } else {
                        $this->properties[$attribute] = $value;
                    }
                } else {
                    $this->properties[$attribute] = $this->{'set' . ucfirst($attribute)}($value);
                }
            }
        }
    }

    /**
     * @param string $method
     * @param array $arg
     * @return mixed
     */
    public function __call($method, $arg)
    {
        $property = Str::camel(substr($method, 3));
        if ($this->reflection->hasProperty($property)) {
            return $arg[0];
        }
        throw new \InvalidArgumentException(
            sprintf('Property "%s" was not found in class %s', $property, get_class($this))
        );
    }

    /**
     * @param string $class
     * @param array $values
     * @return array
     */
    public function setValuesDefault(string $class, array $values): array
    {
        return (new $class($values))->getProperties();
    }

    /**
     * @return array
     */
    public function getProperties()
    {
        return $this->properties;
    }

}