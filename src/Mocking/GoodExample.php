<?php
namespace App\Mocking;

class GoodExample
{
	/** @var DependencyOne */
	private $handler;

	/** @var DependencyTwo */
	private $dep;

	public function __construct(DependencyOne $handler)
	{
		$this->handler = $handler;
	}

	public function processForm(int $userId, array $formData): void
	{
		$user       = $this->handler->getById($userId);
		$user->name = $formData['name'];

		$this->handler->save($user);
	}

	// -----------------------------------------

	public function setDependency(DependencyInterface $dep): void
	{
		$this->dep = $dep;
	}

	public function usingFinalMethod(): void
	{
		$this->dep->setUser((object)['id' => 12]);
	}
}