<?php

namespace Libs;

class Pager
{
	public static function render($filename, array $params) {
		extract($params);
		ob_start();
		include __DIR__."/../views/".$filename.".php";
		return ob_get_clean();
	}
}
