<?php
	/**
	 * VPS Functionality
	 * @author Joe Huss <detain@interserver.net>
	 * @copyright 2018
	 * @package MyAdmin
	 * @category VPS
	 */

	/**
	 * Adds Fantastico to a VPS
	 * @return void
	 */
	function vps_add_fantastico() {
		function_requirements('class.AddServiceAddon');
		$addon = new AddServiceAddon();
		$addon->load(__FUNCTION__, 'Fantastico', 'vps', VPS_FANTASTICO_COST);
		$addon->process();
	}
