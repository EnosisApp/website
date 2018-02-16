<?php

namespace Libs;

class DB
{
	public static function GetInstance() {
		try {
			$db = new \PDO('mysql:host=enosisapp.fr;dbname=baterytests', 'baterytests', 'divndsfivndfkjvndfjvndkvnsdjvnisdnv');
			$db->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
		} catch(PDOException $e) {
			echo "Error while connecting Database.";
			die();
		}
		return $db;
	}
}
