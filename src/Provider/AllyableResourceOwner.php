<?php

namespace Nathanhennig\OAuth2\Client\Provider;

use League\OAuth2\Client\Provider\ResourceOwnerInterface;
use League\OAuth2\Client\Tool\ArrayAccessorTrait;

class AllyableResourceOwner implements ResourceOwnerInterface
{
    use ArrayAccessorTrait;
    /**
     * Raw response
     *
     * @var array
     */
    protected $response;

    /**
     * Creates new resource owner.
     *
     * @param array  $response
     */
    public function __construct(array $response = array())
    {
        $this->response = $response;
    }

    /**
     * Get resource owner id
     *
     * @return string
     */
    public function getId()
    {
        return $this->getValueByKey($this->response, 'sub');
    }

    /**
     * Get resource owner email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->getValueByKey($this->response, 'email');
    }

    /**
     * Get resource owner family name
     *
     * @return string
     */
    public function getFamilyName()
    {
        return $this->getValueByKey($this->response, 'family_name');
    }

    /**
     * Get resource owner given name
     *
     * @return string
     */
    public function getGivenName()
    {
        return $this->getValueByKey($this->response, 'given_name');
    }

    /**
     * Get resource owner name
     *
     * @return string
     */
    public function getName()
    {
        return $this->getValueByKey($this->response, 'name');
    }

    /**
     * Get resource owner preferred username
     *
     * @return string
     */
    public function getPreferredUserName()
    {
        return $this->getValueByKey($this->response, 'preferred_username');
    }

    /**
     * Return all of the owner details available as an array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->response;
    }
}
