<?php

namespace Detain\MyAdminVpsFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

class Plugin {

	public static $name = 'Fantastico VPS Addon';
	public static $description = 'Allows selling of Fantastico Licenses as a VPS Addon.  More info at https://www.netenberg.com/fantastico.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.';
	public static $module = 'vps';
	public static $type = 'addon';


	public function __construct() {
	}

	public static function getHooks() {
		return [
			self::$module.'.load_addons' => [__CLASS__, 'getAddon'],
			self::$module.'.settings' => [__CLASS__, 'getSettings'],
		];
	}

	public static function getAddon(GenericEvent $event) {
		$service = $event->getSubject();
		function_requirements('class.Addon');
		$addon = new \Addon();
		$addon->setModule(self::$module)
			->set_text('Fantastico')
			->set_cost(VPS_FANTASTICO_COST)
			->set_require_ip(TRUE)
			->set_enable([__CLASS__, 'doEnable'])
			->set_disable([__CLASS__, 'doDisable'])
			->register();
		$service->addAddon($addon);
	}

	public static function doEnable(\Service_Order $serviceOrder, $repeatInvoiceId, $regexMatch = FALSE) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		require_once __DIR__.'/../../../../include/licenses/license.functions.inc.php';
		myadmin_log(self::$module, 'info', self::$name.' Activation', __LINE__, __FILE__);
		function_requirements('activate_fantastico');
		activate_fantastico($serviceInfo[$settings['PREFIX'].'_ip'], 2);
		$GLOBALS['tf']->history->add($settings['TABLE'], 'add_fantastico', $serviceInfo[$settings['PREFIX'].'_id'], $serviceInfo[$settings['PREFIX'].'_ip'], $serviceInfo[$settings['PREFIX'].'_custid']);
	}

	public static function doDisable(\Service_Order $serviceOrder, $repeatInvoiceId, $regexMatch = FALSE) {
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		myadmin_log(self::$module, 'info', self::$name.' Deactivation', __LINE__, __FILE__);
		$email = $settings['TBLNAME'].' ID: '.$serviceInfo[$settings['PREFIX'].'_id'].'<br>'.$settings['TBLNAME'].' Hostname: '.$serviceInfo[$settings['PREFIX'].'_hostname'].'<br>Repeat Invoice: '.$repeatInvoiceId.'<br>Description: '.self::$name.'<br>';
		$subject = $settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Canceled '.self::$name;
		$headers = '';
		$headers .= 'MIME-Version: 1.0'.EMAIL_NEWLINE;
		$headers .= 'Content-type: text/html; charset=UTF-8'.EMAIL_NEWLINE;
		$headers .= 'From: '.$settings['TITLE'].' <'.$settings['EMAIL_FROM'].'>'.EMAIL_NEWLINE;
		admin_mail($subject, $email, $headers, false, 'admin_email_vps_cpanel_canceled.tpl');
	}

	public static function getSettings(GenericEvent $event) {
		$settings = $event->getSubject();
		$settings->add_text_setting(self::$module, 'Addon Costs', 'vps_fantastico_cost', 'VPS Fantastico License:', 'This is the cost for purchasing a fantastico license on top of a VPS.', $settings->get_setting('VPS_FANTASTICO_COST'));
	}
}
