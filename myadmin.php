<?php
/* TODO:
 - service type, category, and services  adding
 - dealing with the SERVICE_TYPES_fantastico define
 - add way to call/hook into install/uninstall
*/
return [
	'name' => 'Fantastico Licensing VPS Addon',
	'description' => 'Allows selling of Fantastico Server and VPS License Types.  More info at https://www.netenberg.com/fantastico.php',
	'help' => 'It provides more than one million end users the ability to quickly install dozens of the leading open source content management systems into their web space.  	Must have a pre-existing cPanel license with cPanelDirect to purchase a fantastico license. Allow 10 minutes for activation.',
	'module' => 'vps',
	'author' => 'detain@interserver.net',
	'home' => 'https://github.com/detain/myadmin-fantastico-vps-addon',
	'repo' => 'https://github.com/detain/myadmin-fantastico-vps-addon',
	'version' => '1.0.0',
	'type' => 'addon',
	'hooks' => [
		'vps.load_addons' => ['Detain\MyAdminVpsFantastico\Plugin', 'Load'],
		'vps.settings' => ['Detain\MyAdminVpsFantastico\Plugin', 'Settings'],
		/* 'function.requirements' => ['Detain\MyAdminVpsFantastico\Plugin', 'Requirements'],
		'licenses.activate' => ['Detain\MyAdminVpsFantastico\Plugin', 'Activate'],
		'licenses.change_ip' => ['Detain\MyAdminVpsFantastico\Plugin', 'ChangeIp'],
		'ui.menu' => ['Detain\MyAdminVpsFantastico\Plugin', 'Menu'] */
	],
];
