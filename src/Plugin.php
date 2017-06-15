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
		$addon->set_module('vps')
			->set_text('Fantastico')
			->set_cost(VPS_FANTASTICO_COST)
			->set_require_ip(true)
			->set_enable(['Detain\MyAdminVpsFantastico\Plugins', 'Enable'])
			->set_disable()
			->register();
		$service->add_addon($addon);
	}

	public static function Enable($service_order) {
		$service_info = $service_order->get_service_info();
		$settings = get_module_settings($service_order->get_module());
		require_once 'include/licenses/license.functions.inc.php';
		function_requirements('activate_fantastico');
		activate_fantastico($service_info[$settings['PREFIX'] . '_ip'], 2);
		$GLOBALS['tf']->history->add($settings['TABLE'], 'add_fantastico', $service_info[$settings['PREFIX'] . '_id'], $service_info[$settings['PREFIX'] . '_ip'], $service_info[$settings['PREFIX'] . '_custid']);
	}

	public static function Disable($service_order) {
	}

	public static function Settings(GenericEvent $event) {
		$module = 'vps';
		$settings = $event->getSubject();
		$settings->add_text_setting($module, 'Addon Costs', 'vps_fantastico_cost', 'VPS Fantastico License:', 'This is the cost for purchasing a fantastico license on top of a VPS.', $settings->get_setting('VPS_FANTASTICO_COST'));
	}
}
