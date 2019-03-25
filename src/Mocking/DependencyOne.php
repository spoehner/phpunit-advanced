<?php
namespace App\Mocking;

class DependencyOne
{
	public function getById(int $id)
	{
		return (object)[
			'id'   => $id,
			'name' => 'Tester',
		];
	}

	public function save(\stdClass $user)
	{
	}
}