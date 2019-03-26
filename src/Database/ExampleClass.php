<?php
namespace App\Database;

class ExampleClass
{
	private $db;

	public function __construct(\PDO $db)
	{
		$this->db = $db;
	}

	public function changeUserName($userId, $newUserName)
	{
		$stmt = $this->db->prepare('SELECT * FROM users WHERE id = ?');
		$stmt->execute([$userId]);
		$user           = $stmt->fetchObject();
		$user->username = $newUserName;
		$upStmt         = $this->db->prepare('UPDATE users SET username = ? WHERE id = ?');
		$upStmt->execute([$user->username, $user->id]);
	}
}