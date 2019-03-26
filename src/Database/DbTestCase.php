<?php
namespace App\Database;

use PHPUnit\Framework\TestCase;

class DbTestCase extends TestCase
{
	private   $db;

	protected $tables = [];

	protected function tearDown(): void
	{
		parent::tearDown();
		$this->db     = null; // destroy db
		$this->tables = [];
	}

	private function create()
	{
		$this->db = new \PDO('sqlite::memory:');
		$schema   = include(__DIR__.'/schema.php');
		foreach ($this->tables as $table) {
			if (!isset($schema[$table])) {
				throw new \InvalidArgumentException("Missing schema for $table");
			}
			$this->db->query('DROP TABLE IF EXISTS '.$table);
			$this->db->query('CREATE TABLE `'.$table.'` ('.$schema[$table].')');
		}
	}

	/*
	In Tests ohne DB entsteht kein Overhead wenn wir die Verbindung erst bei Bedarf erzeugen.
	*/
	protected function getDb()
	{
		if ($this->db === null) {
			$this->create();
		}

		return $this->db;
	}
}