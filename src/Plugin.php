<?php

namespace Detain\MyAdminVpsFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public function __construct() {
	}

	public static function Load(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->set_module('vps')->set_text('Fantastico')->set_cost(VPS_FANTASTICO_COST)
			->set_require_ip(true)->set_enable(function() {
				require_once 'include/licenses/license.functions.inc.php';
			})->set_disable(function() {
			})->register();
		$service->add_addon($addon);
	}

	public static function Settings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_fantastico_cost', 'VPS Fantastico License:', 'This is the cost for purchasing a fantastico license on top of a VPS.', $settings->get_setting('VPS_FANTASTICO_COST'));
	}
}
