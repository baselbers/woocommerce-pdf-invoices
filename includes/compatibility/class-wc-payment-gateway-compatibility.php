<?php
/**
 * WooCommerce Plugin Framework
 *
 * This source file is subject to the GNU General Public License v3.0
 * that is bundled with this package in the file license.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.html
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@skyverge.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade the plugin to newer
 * versions in the future. If you wish to customize the plugin for your
 * needs please refer to http://www.skyverge.com
 *
 * @package   SkyVerge/WooCommerce/Compatibility
 * @author    SkyVerge
 * @copyright Copyright (c) 2013-2017, SkyVerge, Inc.
 * @license   http://www.gnu.org/licenses/gpl-3.0.html GNU General Public License v3.0
 */

defined( 'ABSPATH' ) or exit;

if ( ! class_exists( 'BEWPI_WC_Payment_Gateway_Compatibility' ) ) :

	/**
	 * Backports the \WC_Payment_Gateway class.
	 */
	class BEWPI_WC_Payment_Gateway_Compatibility extends DateTime {

		/**
		 * Get method title.
		 *
		 * @param WC_Payment_Gateway $payment_gateway WC payment gateway object.
		 *
		 * @return mixed
		 */
		public static function get_method_title( $payment_gateway ) {

			if ( method_exists( $payment_gateway, 'get_method_title' ) ) {
				$method_title = $payment_gateway->get_method_title();
			} else {
				$method_title = $payment_gateway->method_title;
			}

			return $method_title;
		}

	}

endif;
