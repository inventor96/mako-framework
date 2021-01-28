<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;

/**
 * Has many relation.
 */
class HasMany extends HasOneOrMany
{
	/**
	 * Eager loads related records and matches them with their originating records.
	 *
	 * @param array         &$results Originating records
	 * @param string        $relation Relation name
	 * @param \Closure|null $criteria Relation criteria
	 * @param array         $includes Includes passed from the originating record
	 */
	public function eagerLoad(array &$results, string $relation, ?Closure $criteria, array $includes): void
	{
		$this->model->setIncludes($includes);

		$grouped = [];

		if($criteria !== null)
		{
			$criteria($this);
		}

		$foreignKey = $this->getForeignKey();

		foreach($this->eagerLoadChunked($this->keys($results)) as $related)
		{
			$grouped[$related->getRawColumnValue($foreignKey)][] = $related;
		}

		foreach($results as $result)
		{
			$result->setRelated($relation, $this->createResultSet($grouped[$result->getPrimaryKeyValue()] ?? []));
		}
	}

	/**
	 * Fetches a related result set from the database.
	 *
	 * @return \mako\database\midgard\ResultSet
	 */
	protected function fetchRelated()
	{
		return $this->all();
	}
}
