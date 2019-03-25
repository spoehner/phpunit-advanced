<?php
namespace App\Mocking;

interface DependencyInterface
{
	public function getUser();

	public function setUser(\stdClass $user);
}