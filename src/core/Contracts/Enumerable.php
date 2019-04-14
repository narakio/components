<?php namespace Naraki\Core\Contracts;

interface Enumerable
{
    /**
     * @param string $prefix
     * @return mixed
     */
    public static function getConstants($prefix = null);

    /**
     * Whether a constant exists.
     *
     * @param string $name
     * @param bool $strict
     *
     * @return bool
     */
    public static function isValidName($name, $strict = false);

    /**
     * @param mixed $value
     *
     * @return bool
     */
    public static function isValidValue($value);

    /**
     * Get the name of a constant by passing its number,
     * i.e. what is the constant whose value is 64?
     *
     * @param mixed $id
     * @return string|null The actual name of the constant as is in the code, uppercase.
     */
    public static function getConstantNameByID($id);

    /**
     * @param int $id
     * @return boolean
     */
    public static function getConstantByID($id);

    /**
     * Get the name of a constant by passing its number,
     * i.e. what is the constant whose value is 64?
     *
     * @param mixed $id
     *
     * @param bool $presentable
     * @return string The lower case string
     */
    public static function getConstantName($id, $presentable = false);

    /**
     * @param $name
     *
     * @return mixed
     */
    public static function getConstant($name);

    public static function getModelPresentableName($entityID);

    /**
     * Gets translated names of class constants
     * i.e [1=>First Constant, 2=>Second Constant]
     *
     * @return array
     */
    public static function getPresentableConstants();

}