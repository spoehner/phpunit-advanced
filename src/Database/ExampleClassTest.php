<?php
namespace App\Database;

class ExampleClassTest extends DbTestCase
{
	public function testChangeUserName()
	{
		/*
		 * Wir definieren welche Tabellen verwendet werden sollen. Dies kann auch in einer setUp() für die ganze Klasse erfolgen.
		 */
		$this->tables = ['users'];

		/*
		 * Aufbau der DB-Verbindung.
		 */
		/** @var \PDO $db */
		$db = $this->getDb();

		/*
		 * Seed eines Datensatzes in die Tabelle.
		 */
		$db->exec('INSERT INTO users (id, username) VALUES (2, "old name")');

		/*
		 * Normaler Testaufruf.
		 */
		$class = new ExampleClass($db);
		$class->changeUserName(2, 'new name');

		/*
		 * Laden des Datensatzes und Prüfen der erwarteten Änderungen.
		 */
		$user = $db->query('SELECT * FROM users WHERE id = 2')->fetchObject();
		$this->assertEquals('new name', $user->username);
	}
}