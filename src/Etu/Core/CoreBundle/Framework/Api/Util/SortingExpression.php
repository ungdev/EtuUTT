<?php

namespace Etu\Core\CoreBundle\Framework\Api\Util;

/**
 * A class to understand URL formatted sort by expressions
 */
class SortingExpression
{
	/**
	 * @param string $expression
	 * @param array $validFields
	 * @return array
	 */
	public static function getOrderBy($expression, array $validFields)
	{
		$parts = explode(',', $expression);
		$fields = array();

		foreach ($parts as $part) {
			$part = explode(':', $part);

			$order = 'ASC';
			$field = $part[0];

			if (isset($part[1]) && in_array(strtoupper($part[1]), array('ASC', 'DESC'))) {
				$order = strtoupper($part[1]);
			}

			if (in_array($field, $validFields)) {
				$fields[$field] = $order;
			} else {
				return false;
			}
		}

		return $fields;
	}
}
