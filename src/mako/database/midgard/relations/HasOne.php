<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\database\midgard\relations;

use Closure;

/**
 * Has one relation.
 *
 * @author Frederic G. Østby
 */
class HasOne extends HasOneOrMany
{
	/**
	 * Eager loads related records and matches them with their parent records.
	 *
	 * @param array         &$results Parent records
	 * @param string        $relation Relation name
	 * @param \Closure|null $criteria Relation criteria
	 * @param array         $includes Includes passed from the parent record
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
			$grouped[$related->getRawColumnValue($foreignKey)] = $related;
		}

		foreach($results as $result)
		{
			$result->setRelated($relation, $grouped[$result->getPrimaryKeyValue()] ?? false);
		}
	}

	/**
	 * Returns related a record from the database.
	 *
	 * @return false|\mako\database\midgard\ORM
	 */
	public function getRelated()
	{
		return $this->first();
	}
}
