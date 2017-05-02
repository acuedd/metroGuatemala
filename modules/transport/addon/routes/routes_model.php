<?php
namespace RoutesModel{

	use kernel\Model\model;

	/**
 * Created by PhpStorm.
 * User: edwardacu
 * Date: 02/05/17
 * Time: 15:16
 */
	include_once("modules/transport/transport_model.php");

	class routes_model extends \transport_model implements model
	{
		private static $_instance;

		public function __construct(){
			parent::__construct();
		}

		public static function getInstance() {
			if (!(self::$_instance instanceof self)) {
				self::$_instance = new self();
			}
			return self::$_instance;
		}
	}
}