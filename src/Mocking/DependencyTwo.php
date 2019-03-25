<?php
namespace App\Mocking;

class DependencyTwo implements DependencyInterface
{
	public function getUser()
	{
		return (object)['username' => 'testuser'];
	}

	final public function setUser(\stdClass $user)
	{
		// not important
	}
}