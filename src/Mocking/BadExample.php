<?php
namespace App\Mocking;

class BadExample
{
	public function processForm(int $userId, array $formData): void
	{
		$handler = new DependencyOne();

		$user       = $handler->getById($userId);
		$user->name = $formData['name'];

		$handler->save($user);
	}
}