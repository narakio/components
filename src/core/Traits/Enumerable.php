<?php namespace Naraki\Core\Traits;

trait Enumerable
{
    private static $constCacheArray = null;

    /**
     * @param string $prefix
     * @return mixed
     */
    public static function getConstants($prefix = null)
    {
        if (static::$constCacheArray == null) {
            static::$constCacheArray = [];
        }
        if (!is_null($prefix)) {
            $fn = function ($val, $key) use ($prefix) {
                return is_int($val) && strpos($key, $prefix) !== false;
            };
        } else {
            $fn = function ($val) use ($prefix) {
                return is_int($val);
            };
        }

        $calledClass = get_called_class();
        if (!array_key_exists($calledClass, static::$constCacheArray)) {
            try {
                $reflect = new \ReflectionClass($calledClass);
            } catch (\ReflectionException $e) {
                throw new \UnexpectedValueException(sprintf('Class %s does not exist.', $calledClass));
            }

            static::$constCacheArray[$calledClass] = array_filter(
                $reflect->getConstants(), $fn, ARRAY_FILTER_USE_BOTH
            );
        }

        return static::$constCacheArray[$calledClass];
    }

    /**
     * Whether a constant exists.
     *
     * @param string $name
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false)
    {
        $constants = static::getConstants();
        if ($strict) {
            return array_key_exists($name, $constants);
        }

        $keys = array_map('strtolower', array_keys($constants));

        return in_array(strtolower($name), $keys);
    }

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isValidValue($value)
    {
        $values = array_values(static::getConstants());

        return in_array($value, $values, $strict = true);
    }

    /**
     * Get the name of a constant by passing its number,
     * i.e. what is the constant whose value is 64?
     *
     * @param mixed $id
     * @return string|null The actual name of the constant as is in the code, uppercase.
     */
    public static function getConstantNameByID($id)
    {
        $constants = array_flip(static::getConstants());
        if (isset($constants[$id])) {
            return $constants[$id];
        }
        return null;
    }

    /**
     * @param int $id
     * @return boolean
     */
    public static function getConstantByID($id)
    {
        $constants = array_flip(static::getConstants());
        if (isset($constants[$id])) {
            return $constants[$id];
        }
        return null;
    }

    /**
     * Get the name of a constant by passing its number,
     * i.e. what is the constant whose value is 64?
     *
     * @param mixed $id
     *
     * @param bool $presentable
     * @return string The lower case string
     */
    public static function getConstantName($id, $presentable = false)
    {
        $name = static::getConstantNameByID($id);
        if (!is_null($name)) {
            if ($presentable) {
                return trans(sprintf('general.enumerables.%s', strtolower($name)));
            }
            return strtolower($name);
        }
        throw new \UnexpectedValueException(sprintf('Constant %s does not exist.', $id));
    }

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getConstant($name)
    {
        try {
            $reflect = new \ReflectionClass(static::class);
        } catch (\ReflectionException $e) {
        }

        $id = $reflect->getConstant(strtoupper($name));
        if (!is_null($id)) {
            return $id;
        }
        throw new \UnexpectedValueException(sprintf('Constant %s does not exist.', $name));
    }

    public static function getModelPresentableName($entityID)
    {
        $entities = array_flip(static::getConstants());
        if (isset($entities[$entityID])) {
            return trans(sprintf('general.enumerables.%s', $entities[$entityID]));
        }
        return null;
    }

    /**
     * Gets translated names of class constants
     * i.e [1=>First Constant, 2=>Second Constant]
     *
     * @return array
     */
    public static function getPresentableConstants()
    {
        $output = [];
        $constants = static::getConstants();
        foreach ($constants as $name => $id) {
            $output[$id] = trans(sprintf('general.enumerables.%s', strtolower($name)));
        }
        return $output;
    }

}