<?php
class ServiceResponse
{
        /**
     * Response storage
     *
     * @var array
     */
    public $data;

    /**
     * Metadata storage
     *
     * @var array
     */
    public $metadata;

    /**
     * @var array
     */
    public $errors;

    /**
     * @var array
     */
    public $status;

    /**
     * Constructor
     *
     */
    public function __construct()
    {
        $this->data     = array();
        $this->metadata = array();
        $this->errors   = array();
        $this->status   = true;
    }

    /**
     * Returns data
     *
     * @return array An array of data
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Sets data
     *
     * @return void
     */
    public function setData($data)
    {
        $this->data = $data;
    }

    /**
     * Returns metadata
     *
     * @return array An array of metdata
     */
    public function getMetadata()
    {
        return $this->metadata;
    }

    /**
     * Returns metadata keys
     *
     * @return array An array of meta keys
     */
    public function metaKeys()
    {
        return array_keys($this->metadata);
    }

    /**
     * Set a metadata value
     *
     * @param string $key   The key
     * @param mixed  $value The value
     */
    public function addMeta($key, $value)
    {
        $this->metadata[$key] = $value;
    }

    /**
     * Retrieve a metadata value
     *
     * @param string $key   The key
     * @param mixed  $value The default value
     *
     * @return mixed
     */
    public function getMeta($key, $default = null)
    {
        if ($this->hasMeta($key)) {
            return $this->metadata[$key];
        }

        return $default;
    }

    /**
     * Returns true if the key is defined in metadata
     *
     * @param string $key The key
     *
     * @return bool true if the key exists, false otherwise
     */
    public function hasMeta($key)
    {
        return array_key_exists($key, $this->metadata);
    }

    /**
     * Removes value from metadata
     *
     * @param string $key The key
     */
    public function removeMeta($key)
    {
        unset($this->metadata[$key]);
    }

    /**
     * Check if Response is Ok
     *
     * @return boolean
     */
    public function isOk()
    {
        if (count($this->errors) === 0) {
            return true;
        }

        return false;
    }

    /**
     *
     * @return ConstraintViolationList
     */
    public function getErrors()
    {
        return $this->errors;
    }

    /**
     * Adds a constaint violation to the list
     *
     * @param string $error
     */
    public function addError($error, $message = '')
    {
        $this->errors[$error] = $message;
        $this->status = false;
    }

    /**
     *
     * @param array $errors
     */
    public function addErrors(array $errors)
    {
        $this->errors = $errors;
    }
}
