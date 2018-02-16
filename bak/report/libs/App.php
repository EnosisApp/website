<?php

namespace Libs;

class App
{
	public static function Login() {
		if(isset($_GET['ticket'])) {
			$ret = file_get_contents("https://cas.u-bordeaux.fr/serviceValidate?service=https://enosisapp.fr/report/index.php&ticket=".$_GET['ticket']);
			header('Content-type: text/plain');
			echo $ret;

			$doc = new \DOMDocument();
			$doc->loadXML($ret);
			if(!$doc->getElementsByTagName('authenticationFailure')->length) { // If auth worked
				$_SESSION['user'] = $doc->getElementsByTagName('user')['0']->nodeValue;
				header('Location: ?');
			}
			die();
		} else {
			header('Location: https://cas.u-bordeaux.fr/login?service=https://enosisapp.fr/report/index.php');
			die();
		}
	}

	public static function Logout() {
		session_destroy();
		session_unset();
		header("Location: ?");
		die();
	}

	public static function checkField($str) {
		return isset($_POST[$str]) && !empty($_POST[$str]);
	}

	public static function Form() {
		$db = DB::GetInstance();
		if($_SERVER['REQUEST_METHOD'] === "POST") {
			if(self::checkField('day1')) {
				$req = $db->prepare('SELECT * FROM entries WHERE user=:user');
				$req->execute(['user' => $_SESSION['user']]);
				if($req->rowCount()) {
					$req = $db->prepare('UPDATE entries SET day1=:day1 WHERE user=:user');
					$req->execute(['day1' => $_POST['day1'], 'user' => $_SESSION['user']]);
				} else {
					$req = $db->prepare('INSERT INTO entries SET day1=:day1, user=:user');
					$req->execute(['day1' => $_POST['day1'], 'user' => $_SESSION['user']]);
				}
			}
			if(self::checkField('day2')) {
				$req = $db->prepare('SELECT COUNT(*) FROM entries WHERE user=:user');
				$req->execute(['user' => $_SESSION['user']]);
				if($req->rowCount()) {
					$req = $db->prepare('UPDATE entries SET day2=:day2 WHERE user=:user');
					$req->execute(['day2' => $_POST['day2'], 'user' => $_SESSION['user']]);
				}
			}
			if(self::checkField('feeling')) {
				$req = $db->prepare('UPDATE entries SET feeling=:feeling WHERE user=:user');
				$req->execute(['feeling' => $_POST['feeling'], 'user' => $_SESSION['user']]);
			}
		}

		$req = $db->prepare('SELECT * FROM entries WHERE user=:user');
		$req->execute(['user' => $_SESSION['user']]);

		return Pager::render('form', ['entries' => $req->fetch(\PDO::FETCH_ASSOC)]);
	}
}
