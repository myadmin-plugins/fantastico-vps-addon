<?php

namespace Detain\MyAdminVpsFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public function __construct() {
	}

	public static function Load(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('Addon');
		$addon = new \Addon();
		$addon->set_module('vps')->set_text('Fantastico')->set_cost(VPS_FANTASTICO_COST)
			->set_require_ip(true)->set_enable(function() {
				require_once 'include/licenses/license.functions.inc.php';
			})->set_disable(function() {
			})->register();
	}

}
