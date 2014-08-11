<?php

namespace Etu\Core\ApiBundle\Framework\Embed;

use Symfony\Component\HttpFoundation\Request;

class EmbedBag
{
    /**
     * @var array
     */
    protected $fields;

    /**
     * @param array $fields
     */
    public function __construct($fields = [])
    {
        $this->fields = $fields;
    }

    /**
     * @param Request $request
     * @return EmbedBag
     */
    public static function createFromRequest(Request $request)
    {
        $fields = array_map('trim', explode(',', $request->query->get('embed', '')));

        return new self($fields);
    }

    /**
     * @param $key
     * @return bool
     */
    public function has($key)
    {
        return in_array($key, $this->fields);
    }

    /**
     * @param array $availableFields
     * @return array
     */
    public function getMap($availableFields = [])
    {
        $map = [];

        foreach ($availableFields as $field) {
            $map[$field] = $this->has($field);
        }

        return $map;
    }
}