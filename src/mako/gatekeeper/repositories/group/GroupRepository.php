<?php

/**
 * @copyright Frederic G. Østby
 * @license   http://www.makoframework.com/license
 */

namespace mako\gatekeeper\repositories\group;

use mako\gatekeeper\exceptions\GatekeeperException;

use function in_array;
use function vsprintf;

/**
 * Group repository.
 *
 * @method \mako\gatekeeper\entities\group\Group      createGroup(array $properties = [])
 * @method \mako\gatekeeper\entities\group\Group|null getByIdentifier($identifier)
 */
class GroupRepository implements GroupRepositoryInterface
{
	/**
	 * Model name.
	 *
	 * @var string
	 */
	protected $model;

	/**
	 * Group identifier.
	 *
	 * @var string
	 */
	protected $identifier = 'name';

	/**
	 * Constructor.
	 *
	 * @param string $model Model name
	 */
	public function __construct(string $model)
	{
		$this->model = $model;
	}

	/**
	 * Returns a model instance.
	 *
	 * @return \mako\gatekeeper\entities\group\Group
	 */
	protected function getModel()
	{
		$model = $this->model;

		return new $model;
	}

	/**
	 * {@inheritDoc}
	 */
	public function createGroup(array $properties = [])
	{
		$group = $this->getModel();

		foreach($properties as $property => $value)
		{
			$group->$property = $value;
		}

		$group->save();

		return $group;
	}

	/**
	 * Sets the user identifier.
	 *
	 * @param string $identifier User identifier
	 */
	public function setIdentifier(string $identifier): void
	{
		if(!in_array($identifier, ['name', 'id']))
		{
			throw new GatekeeperException(vsprintf('Invalid identifier [ %s ].', [$identifier]));
		}

		$this->identifier = $identifier;
	}

	/**
	 * Fetches a group by its name.
	 *
	 * @param  string                                     $name Group name
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getByName(string $name)
	{
		return $this->getModel()->where('name', '=', $name)->first();
	}

	/**
	 * Fetches a group by its id.
	 *
	 * @param  int                                        $id Group id
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getById(int $id)
	{
		return $this->getModel()->where('id', '=', $id)->first();
	}

	/**
	 * {@inheritDoc}
	 *
	 * @return \mako\gatekeeper\entities\group\Group|null
	 */
	public function getByIdentifier($identifier)
	{
		switch($this->identifier)
		{
			case 'name':
				return $this->getByName($identifier);
			case 'id':
				return $this->getById($identifier);
			default:
				throw new GatekeeperException(vsprintf('Invalid identifier [ %s ].', [$identifier]));
		}
	}
}
