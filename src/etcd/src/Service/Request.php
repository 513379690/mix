<?php

namespace Mix\Etcd\Service;

use Mix\Micro\Register\RequestInterface;
use Mix\Micro\Register\ValueInterface;

/**
 * Class Request
 * @package Mix\Etcd\Service
 */
class Request implements RequestInterface
{

    /**
     * @var string
     */
    protected $name = '';

    /**
     * @var string
     */
    protected $type = '';

    /**
     * @var ValueInterface[]
     */
    protected $values;

    /**
     * Request constructor.
     * @param string $name
     * @param string $type
     */
    public function __construct(string $name, string $type)
    {
        $this->name = $name;
        $this->type = $type;
    }

    /**
     * Get name
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Get type
     * @return string
     */
    public function getType(): string
    {
        return $this->type;
    }

    /**
     * Get values
     * @return ValueInterface[]|null
     */
    public function getValues()
    {
        return $this->values;
    }

    /**
     * Set add value
     * @param ValueInterface $value
     */
    public function getAddedValue(ValueInterface $value)
    {
        $this->values[] = $value;
    }

    /**
     * Json serialize
     * @return array
     */
    public function jsonSerialize()
    {
        $data = [];
        foreach ($this as $key => $val) {
            $data[$key] = $val;
        }
        return $data;
    }

}
