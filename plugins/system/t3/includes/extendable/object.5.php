<?php
/**
 * ------------------------------------------------------------------------
 * T3 Framework for Joomla!
 * ------------------------------------------------------------------------
 * Copyright (C) 2004-2013 JoomlArt.com, Ltd.
 * License - GNU/GPL
 * ------------------------------------------------------------------------
 */

// No direct access.
defined('_JEXEC') or die();

class ObjectExtendable
{
    /**
     * Objects extended into this object.
     *
     * @var array
     */
    protected array $_extendableObjects = [];

    /**
     * Extend this object with another object or array of objects.
     *
     * @param object|array $object Object or array of objects.
     *
     * @return void
     */
    public function _extend($object): void
    {
        if (is_object($object)) {
            $this->_extendableObjects[] = $object;
        } elseif (is_array($object)) {
            foreach ($object as $item) {
                if (is_object($item)) {
                    $this->_extendableObjects[] = $item;
                }
            }
        }
    }

    /**
     * Get a property from an extended object.
     *
     * @param string $name Property name.
     *
     * @return mixed
     */
    public function __get($name)
    {
        foreach ($this->_extendableObjects as $object) {
            if (property_exists($object, $name)) {
                return $object->$name;
            }
        }

        return null;
    }

    /**
     * Set a property on an extended object.
     *
     * @param string $name  Property name.
     * @param mixed  $value Property value.
     *
     * @return mixed
     */
    public function __set($name, $value)
    {
        foreach ($this->_extendableObjects as $object) {
            if (property_exists($object, $name)) {
                $object->$name = $value;

                return $value;
            }
        }

        return null;
    }

    /**
     * Call a method on this object or one of the extended objects.
     *
     * @param string $name      Method name.
     * @param array  $arguments Method arguments.
     *
     * @return mixed
     */
    public function __call($name, $arguments = [])
    {
        // Try to call a method on this object.
        if (method_exists($this, $name)) {
            return call_user_func_array(
                [$this, $name],
                $arguments
            );
        }

        // Try to call a method on an extended object.
        foreach ($this->_extendableObjects as $object) {
            if (method_exists($object, $name)) {
                return call_user_func_array(
                    [$object, $name],
                    $arguments
                );
            }
        }

        return null;
    }
}