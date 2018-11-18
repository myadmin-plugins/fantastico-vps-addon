<?php

namespace Detain\MyAdminVpsFantastico;

use Symfony\Component\EventDispatcher\GenericEvent;

/**
 * Class Plugin
 *
 * @package Detain\MyAdminVpsFantastico
 */
class Plugin
{
	public static $name = 'Fantastico VPS Addon';
	public static $description = 'Allows selling of Fantastico Licenses as a VPS Addon.  More info at https://www.netenberg.com/fantastico.php';
	public static $help = 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.';
	public static $module = 'vps';
	public static $type = 'addon';

	/**
	 * Plugin constructor.
	 */
	public function __construct()
	{
	}

	/**
	 * @return array
	 */
	public static function getHooks()
	{
		return [
			'function.requirements' => [__CLASS__, 'getRequirements'],
			self::$module.'.load_addons' => [__CLASS__, 'getAddon'],
			self::$module.'.settings' => [__CLASS__, 'getSettings']
		];
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getRequirements(GenericEvent $event)
	{
        /**
         * @var \MyAdmin\Plugins\Loader $this->loader
         */
        $loader = $event->getSubject();
		$loader->add_page_requirement('vps_add_fantastico', '/../vendor/detain/myadmin-fantastico-vps-addon/src/vps_add_fantastico.php');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
	public static function getAddon(GenericEvent $event)
	{
		/**
		 * @var \ServiceHandler $service
		 */
		$service = $event->getSubject();
		function_requirements('class.AddonHandler');
		$addon = new \AddonHandler();
		$addon->setModule(self::$module)
			->set_text('Fantastico')
			->set_cost(VPS_FANTASTICO_COST)
			->set_require_ip(true)
			->setEnable([__CLASS__, 'doEnable'])
			->setDisable([__CLASS__, 'doDisable'])
			->register();
		$service->addAddon($addon);
	}

	/**
	 * @param \ServiceHandler $serviceOrder
	 * @param                $repeatInvoiceId
	 * @param bool           $regexMatch
	 */
	public static function doEnable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)
	{
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		require_once __DIR__.'/../../../../include/licenses/license.functions.inc.php';
		myadmin_log(self::$module, 'info', self::$name.' Activation', __LINE__, __FILE__);
		function_requirements('activate_fantastico');
		activate_fantastico($serviceInfo[$settings['PREFIX'].'_ip'], 2);
		$GLOBALS['tf']->history->add($settings['TABLE'], 'add_fantastico', $serviceInfo[$settings['PREFIX'].'_id'], $serviceInfo[$settings['PREFIX'].'_ip'], $serviceInfo[$settings['PREFIX'].'_custid']);
	}

	/**
	 * @param \ServiceHandler $serviceOrder
	 * @param                $repeatInvoiceId
	 * @param bool           $regexMatch
	 */
	public static function doDisable(\ServiceHandler $serviceOrder, $repeatInvoiceId, $regexMatch = false)
	{
		$serviceInfo = $serviceOrder->getServiceInfo();
		$settings = get_module_settings(self::$module);
		myadmin_log(self::$module, 'info', self::$name.' Deactivation', __LINE__, __FILE__);
		$email = $settings['TBLNAME'].' ID: '.$serviceInfo[$settings['PREFIX'].'_id'].'<br>'.$settings['TBLNAME'].' Hostname: '.$serviceInfo[$settings['PREFIX'].'_hostname'].'<br>Repeat Invoice: '.$repeatInvoiceId.'<br>Description: '.self::$name.'<br>';
		$subject = $settings['TBLNAME'].' '.$serviceInfo[$settings['PREFIX'].'_id'].' Canceled '.self::$name;
		$headers = '';
		$headers .= 'MIME-Version: 1.0'.PHP_EOL;
		$headers .= 'Content-type: text/html; charset=UTF-8'.PHP_EOL;
		$headers .= 'From: '.$settings['TITLE'].' <'.$settings['EMAIL_FROM'].'>'.PHP_EOL;
		admin_mail($subject, $email, $headers, false, 'admin/vps_cpanel_canceled.tpl');
	}

	/**
	 * @param \Symfony\Component\EventDispatcher\GenericEvent $event
	 */
    public static function getSettings(GenericEvent $event)
    {
        /**
         * @var \MyAdmin\Settings $settings
         **/
        $settings = $event->getSubject();
		$settings->add_text_setting(self::$module, _('Addon Costs'), 'vps_fantastico_cost', _('VPS Fantastico License'), _('This is the cost for purchasing a fantastico license on top of a VPS.'), $settings->get_setting('VPS_FANTASTICO_COST'));
	}
}
