<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

use \Aelia\CurrencySwitcher\Logger as Logger;
use \Aelia\CurrencySwitcher\Semaphore as Semaphore;

/**
 * Helper class to handle installation and update of Currency Switcher plugin.
 */
class WC_Aelia_CurrencySwitcher_Install extends WC_Aelia_Install {
	// @var WC_Aelia_CurrencySwitcher_Settings Settings controller instance.
	protected $settings;
	// @var Aelia\CurrencySwitcher\Semaphore The semaphore used to prevent race conditions.
	protected $semaphore;

	// @var array A list of exchange rates. Used for caching.
	protected $exchange_rates = array();

	// @var array A list of the currencies with invalid exchange rates.
	// @since 3.9.6.160408
	protected $invalid_fx_rates = array();

	/**
	 * Returns current instance of the Currency Switcher.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currency_switcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	public function __construct() {
		parent::__construct();

		$this->settings = WC_Aelia_CurrencySwitcher::settings();
	}

	/**
	 * Determines if WordPress maintenance mode is active.
	 *
	 * @return bool
	 */
	protected function maintenance_mode() {
		return file_exists(ABSPATH . '.maintenance') || defined('WP_INSTALLING');
	}

	/**
	 * Indicates if all updates should be re-applied from the beginning. This
	 * should be done only in precise circumstances, and only by administrators.
	 *
	 * @return bool
	 */
	protected function force_all_updates() {
		return isset($_GET[AELIA_CS_ARG_FORCE_ALL_UPDATES]) && ($_GET[AELIA_CS_ARG_FORCE_ALL_UPDATES] == 'go') && is_admin() && current_user_can('manage_options');
	}

	/**
	 * Overrides standard update method to ensure that requirements for update are
	 * in place.
	 *
	 * @param string plugin_id The ID of the plugin.
	 * @param string new_version The new version of the plugin, which will be
	 * stored after a successful update to keep track of the status.
	 * @return bool
	 */
	public function update($plugin_id, $new_version) {
		// Don't run updates while maintenance mode is active
		if($this->maintenance_mode()) {
			return true;
		}

		// If updates should be forced, delete the plugin version from the database.
		// The update procedure will think that it's a first install an re-run all
		// updates
		if($this->force_all_updates()) {
			delete_option($plugin_id);
		}

		$current_version = get_option($plugin_id);
		if(version_compare($current_version, $new_version, '>=')) {
			return true;
		}

		// We need the plugin to be configured before the updates can be applied. If
		// that is not the case, simply return true. The update will be called again
		// at next page load, until it will finally find settings and apply the
		// required changes
		$current_settings = $this->settings->current_settings();
		if(empty($current_settings)) {
			Logger::log(__('No settings found. This means that the plugin has just '.
										 'been installed. Update will run as soon as the settings ' .
										 'are saved.', AELIA_CS_PLUGIN_TEXTDOMAIN));
			return true;
		}

		$this->semaphore = new Semaphore('Aelia_CurrencySwitcher');
		$this->semaphore->initialize();
		if(!$this->semaphore->lock()) {
			Logger::log(__('Plugin Autoupdate - Could not obtain semaphore lock. This may mean that '.
										 'the process has already started, or that the lock is ' .
										 'stuck. Update process will run again later.', AELIA_CS_PLUGIN_TEXTDOMAIN));
			// Return true as the process already running is considered ok
			return true;
		}

		// Set time limit to 10 minutes (updates can take some time)
		@set_time_limit(10 * 60);

		Logger::log(__('Running plugin autoupdate...', AELIA_CS_PLUGIN_TEXTDOMAIN));
		$result = parent::update($plugin_id, $new_version);

		Logger::log(sprintf(__('Autoupdate complete. Result: %s.', AELIA_CS_PLUGIN_TEXTDOMAIN),
												$result));

		// Unlock the semaphore, to allow update to run again later
		$this->semaphore->unlock();

		return $result;
	}

	/**
	 * Converts an amount from a Currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string from_currency The source Currency.
	 * @param string to_currency The destination Currency.
	 * @separam int order The order from which the value was taken. Used mainly
	 * for logging purposes.
	 * @return float The amount converted in the destination currency.
	 */
	public function convert($amount, $from_currency, $to_currency, $order) {
		// If the exchange rate for either the order currency or the base currency
		// cannot be retrieved, it probably means that the plugin has just been
		// installed, or that it hasn't been configured correctly. In such case,
		// returning false will tag the update as "unsuccessful", and it will run
		// again at next page load
		$exchange_rate_msg_details = __('This usually occurs when the Currency Switcher ' .
																		'plugin has not yet been configured and exchange ' .
																		'rates have not been specified. <strong>Please refer to ' .
																		'our knowledge base to learn how to fix it</strong>: ' .
																		'<a href="https://aelia.freshdesk.com/solution/articles/3000017311-i-get-a-warning-saying-that-exchange-rate-could-not-be-retrieved-">I get a warning saying that "Exchange rate could not be retrieved" </a>.',
																		AELIA_CS_PLUGIN_TEXTDOMAIN);

		// Fetch and store the exchange rate for the source currency
		if(!isset($this->exchange_rates[$from_currency])) {
			$this->exchange_rates[$from_currency] = $this->settings->get_exchange_rate($from_currency);
		}

		// If the exchange rate for the source currency is not valid, don't attempt a
		// conversion
		if(($this->exchange_rates[$from_currency] == false) ||
			 !is_numeric($this->exchange_rates[$from_currency]) ||
			 ($this->exchange_rates[$from_currency] <= 0)) {

			if(!in_array($from_currency, $this->invalid_fx_rates)) {
				$this->add_message(E_USER_WARNING,
													 sprintf(__('Exchange rate for currency "%s" could not be retrieved.', AELIA_CS_PLUGIN_TEXTDOMAIN) .
																	 ' ' .
																	 $exchange_rate_msg_details,
																	 $from_currency));
				$this->invalid_fx_rates[] = $from_currency;
			}
			return false;
		}

		// Fetch and store the exchange rate for the target currency
		if(!isset($this->exchange_rates[$to_currency])) {
			$this->exchange_rates[$to_currency] = $this->settings->get_exchange_rate($to_currency);
		}

		// If the exchange rate for the target currency is not valid, don't attempt a
		// conversion
		if(($this->exchange_rates[$to_currency] == false) ||
			 !is_numeric($this->exchange_rates[$to_currency]) ||
			 ($this->exchange_rates[$to_currency] <= 0)) {

			if(!in_array($to_currency, $this->invalid_fx_rates)) {
				$this->add_message(E_USER_WARNING,
													 sprintf(__('Exchange rate for currency "%s" could not be retrieved.', AELIA_CS_PLUGIN_TEXTDOMAIN) .
																	 ' ' .
																	 $exchange_rate_msg_details,
																	 $to_currency));
				$this->invalid_fx_rates[] = $to_currency;
			}
			return false;
		}

		return $this->currency_switcher()->convert($amount, $from_currency, $to_currency, null, false);
	}

	/**
	 * Calculate order totals and taxes in base currency for Orders that have been
	 * generated before version 3.2.10.1402126. This method corrects the calculation
	 * of order totals in base currency, which were incorrectly made taking into
	 * account the exchange markup eventually specified in configuration.
	 * Note: recalculation is made from the 1st of the year onwards, as exchange
	 * rates have changed significantly in the past months and it's not currently
	 * possible to retrieve them at a specific point in time.
	 *
	 * @return bool
	 */
	protected function update_to_3_2_10_1402126() {
		$base_currency = $this->settings->base_currency();

		// Process past orders that were placed in order currency. This will
		// automatically populate all the "_base_currency" fields without a need to
		// make manual recalculations, since the conversion rate would be 1:1 anyway
		$SQL = $this->wpdb->prepare("
			INSERT IGNORE INTO {$this->wpdb->prefix}postmeta	(
				post_id
				,meta_key
				,meta_value
			)
			SELECT
				meta_order.post_id
				,CONCAT(meta_order.meta_key, '_base_currency') as meta_key
				,meta_order.meta_value
			FROM
				{$this->wpdb->prefix}postmeta meta_order
				JOIN
				{$this->wpdb->prefix}postmeta meta_order_currency ON
					(meta_order_currency.post_id = meta_order.post_id) AND
					(meta_order_currency.meta_key = '_order_currency') AND
					(meta_order_currency.meta_value = %s)
				LEFT JOIN
				{$this->wpdb->prefix}postmeta AS meta_order_base_currency ON
					(meta_order_base_currency.post_id = meta_order.post_id) AND
					(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency'))
			WHERE
				(meta_order.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax', '_refund_amount')) AND
				(meta_order_base_currency.meta_value IS NULL);
		", $base_currency);

		$this->add_message(E_USER_NOTICE, __('Processing past orders that were placed in base currency...'));
		$rows_affected = $this->exec($SQL);
		$this->add_message(E_USER_NOTICE, sprintf(__('Done. %s rows affected.'), $rows_affected));

		// Debug
		//var_dump($SQL);die();

		// Retrieve the exchange rates for the orders whose data already got
		// partially converted
		$last_year = date('Y') - 1;
		$SQL = "
			SELECT
				posts.ID AS order_id
				,posts.post_date AS post_date
				,meta_order.meta_key
				,meta_order.meta_value
				-- ,meta_order_base_currency.meta_key AS meta_key_base_currency
				,meta_order_base_currency.meta_value AS meta_value_base_currency
				,meta_order_currency.meta_value AS currency
			FROM
				{$this->wpdb->posts} AS posts
			JOIN
				{$this->wpdb->postmeta} AS meta_order ON
					(meta_order.post_id = posts.ID) AND
					(meta_order.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax', '_refund_amount'))
			LEFT JOIN
				{$this->wpdb->postmeta} AS meta_order_base_currency ON
					(meta_order_base_currency.post_id = posts.ID) AND
					(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency')) AND
					(meta_order_base_currency.meta_value > 0)
			LEFT JOIN
				{$this->wpdb->postmeta} AS meta_order_currency ON
					(meta_order_currency.post_id = posts.ID) AND
					(meta_order_currency.meta_key = '_order_currency')
			WHERE
				(posts.post_type = 'shop_order') AND
				(meta_order.meta_value IS NOT NULL) AND
				(meta_order_base_currency.meta_value IS NULL) AND
				(post_date >= '{$last_year}-01-01 00:00:00')
		";

		$orders_to_update = $this->select($SQL);
		// Debug
		//var_dump($orders_to_update); die();

		foreach($orders_to_update as $order) {
			// If order currency is empty, for whatever reason, no conversion can be
			// performed (it's not possible to assume that a specific currency was
			// used)
			if(empty($order->currency)) {
				Logger::log(sprintf(__('Order %s does not have a currency associated, therefore ' .
															 'it is not possible to determine its value in base currency (%s). ' .
															 'This may lead to imprecise results in the reports.', AELIA_CS_PLUGIN_TEXTDOMAIN),
														$order->order_id,
														$base_currency));

				continue;
			}

			// Try to retrieve the exchange rate used when the order was placed
			$value_in_base_currency = $this->convert($order->meta_value,
																							 $order->currency,
																							 $base_currency,
																							 $order);
			$value_in_base_currency = WC_Aelia_CurrencySwitcher::instance()->float_to_string($value_in_base_currency);

			try {
				update_post_meta($order->order_id,
												 $order->meta_key . '_base_currency',
												 $value_in_base_currency);
			}
			catch(Exception $e) {
				$this->add_message(E_USER_ERROR,
													 sprintf(__('Exception occurred updating base currency values for order %s. ' .
																			'Error: %s.'),
																	 $order->order_id,
																	 $e->getMessage()));
				return false;
			}
		}
		return true;
	}

	/**
	 * Calculate order items totals and taxes in base currency for all orders.
	 * This method adds the line totals in base currency for all the order items
	 * created before Currency Switcher 3.2.11.140227 was installed.
	 *
	 * @return bool
	 */
	protected function update_to_3_3_7_140611() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		// Retrieve the order items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			INSERT INTO {$this->wpdb->prefix}woocommerce_order_itemmeta (
				order_item_id
				,meta_key
				,meta_value
			)
			SELECT
				LINE_ITEMS_DATA.order_item_id
				,LINE_ITEMS_DATA.order_item_meta_key_base_currency
				,LINE_ITEMS_DATA.meta_value_base_currency
			FROM (
				-- Fetch all line items for which the totals in base currency have not been saved yet
				SELECT
					posts.ID AS order_id
					,meta_order_base_currency.meta_value / meta_order.meta_value as exchange_rate
					,WCOI.order_item_id
					,WCOI.order_item_type
					,WCOIM.meta_key
					,WCOIM.meta_value
					,CONCAT(WCOIM.meta_key, '_base_currency') AS order_item_meta_key_base_currency
					,ROUND(WCOIM.meta_value * (meta_order_base_currency.meta_value / meta_order.meta_value), %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS posts
				JOIN
					{$this->wpdb->postmeta} AS meta_order ON
						(meta_order.post_id = posts.ID)
				LEFT JOIN
					{$this->wpdb->postmeta} AS meta_order_base_currency ON
						(meta_order_base_currency.post_id = posts.ID) AND
						(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency')) AND
						(meta_order_base_currency.meta_value > 0)
				-- Order items
				JOIN
					{$this->wpdb->prefix}woocommerce_order_items WCOI ON
						(WCOI.order_id = posts.ID)
				JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM ON
						(WCOIM.order_item_id = WCOI.order_item_id) AND
						(WCOIM.meta_key IN ('_line_subtotal',
											'_line_subtotal_tax',
											'_line_tax',
											'_line_total',
											'tax_amount',
											'shipping_tax_amount'))
				LEFT JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM_TOUPDATE ON
						(WCOIM_TOUPDATE.order_item_id = WCOIM.order_item_id) AND
						(WCOIM_TOUPDATE.meta_key = CONCAT(WCOIM.meta_key, '_base_currency'))
				WHERE
					(WCOIM_TOUPDATE.meta_value IS NULL) AND
					(posts.post_type = 'shop_order') AND
					(meta_order.meta_key = '_order_total') AND
					(meta_order.meta_value IS NOT NULL) AND
					(meta_order_base_currency.meta_value IS NOT NULL)
			) AS LINE_ITEMS_DATA;
		", $price_decimals);

		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating line totals in base currency...'));
		$rows_affected = $this->exec($SQL);

		// Debug
		//var_dump($order_items_to_update);die();
		if($rows_affected === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), $rows_affected));
		}
		return true;
	}

	/**
	 * Calculate order items totals and taxes in base currency for all orders. This
	 * method patches the "discount amount" meta that might not have been calculated
	 * correctly.
	 *
	 * @return bool
	 * @since 3.6.35.150603
	 */
	protected function update_to_3_6_35_150603() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		// Retrieve the order items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			INSERT INTO {$this->wpdb->prefix}woocommerce_order_itemmeta (
				order_item_id
				,meta_key
				,meta_value
			)
			SELECT
				LINE_ITEMS_DATA.order_item_id
				,LINE_ITEMS_DATA.order_item_meta_key_base_currency
				,LINE_ITEMS_DATA.meta_value_base_currency
			FROM (
				-- Fetch all line items for which the totals in base currency have not been saved yet
				SELECT
					posts.ID AS order_id
					,meta_order_base_currency.meta_value / meta_order.meta_value as exchange_rate
					,WCOI.order_item_id
					,WCOI.order_item_type
					,WCOIM.meta_key
					,WCOIM.meta_value
					,CONCAT(WCOIM.meta_key, '_base_currency') AS order_item_meta_key_base_currency
					,ROUND(WCOIM.meta_value * (meta_order_base_currency.meta_value / meta_order.meta_value), %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS posts
				JOIN
					{$this->wpdb->postmeta} AS meta_order ON
						(meta_order.post_id = posts.ID)
				LEFT JOIN
					{$this->wpdb->postmeta} AS meta_order_base_currency ON
						(meta_order_base_currency.post_id = posts.ID) AND
						(meta_order_base_currency.meta_key = CONCAT(meta_order.meta_key, '_base_currency')) AND
						(meta_order_base_currency.meta_value > 0)
				-- Order items
				JOIN
					{$this->wpdb->prefix}woocommerce_order_items WCOI ON
						(WCOI.order_id = posts.ID)
				JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM ON
						(WCOIM.order_item_id = WCOI.order_item_id) AND
						(WCOIM.meta_key IN (
							'discount_amount',
							'discount_amount_tax'
						))
				LEFT JOIN
					{$this->wpdb->prefix}woocommerce_order_itemmeta WCOIM_TOUPDATE ON
						(WCOIM_TOUPDATE.order_item_id = WCOIM.order_item_id) AND
						(WCOIM_TOUPDATE.meta_key = CONCAT(WCOIM.meta_key, '_base_currency'))
				WHERE
					(WCOIM_TOUPDATE.meta_value IS NULL) AND
					(posts.post_type = 'shop_order') AND
					(meta_order.meta_key = '_order_total') AND
					(meta_order.meta_value IS NOT NULL) AND
					(meta_order_base_currency.meta_value IS NOT NULL)
			) AS LINE_ITEMS_DATA;
		", $price_decimals);

		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating discounts in base currency...'));
		$rows_affected = $this->exec($SQL);

		// Debug
		//var_dump($order_items_to_update);die();
		if($rows_affected === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), $rows_affected));
		}
		return true;
	}


	/**
	 * Calculates refund totals in base currency. This method cleans up past
	 * refund calculations, which might be incorrect, and uses the exchange rate
	 * applicable when the refund was created to recalculate the refund totals in
	 * base currency.
	 *
	 * @return bool
	 * @since 3.6.36.150603
	 */
	protected function update_to_3_6_36_150603() {
		$price_decimals = WC_Aelia_CurrencySwitcher::settings()->price_decimals($this->settings->base_currency());

		$px = $this->wpdb->prefix;

		// Retrieve the refunds items for which the values in base currencies will be
		// added/updated
		$SQL = $this->wpdb->prepare("
			SELECT
				REFUNDS_META.refund_id
				,REFUNDS_META.refund_meta_key_base_currency
				,REFUNDS_META.meta_value_base_currency
			FROM (
				-- Fetch all refunds for which the totals in base currency have not been saved yet
				SELECT
					refunds.ID AS refund_id
					,COALESCE(ORDER_META2.meta_value, 0) / COALESCE(ORDER_META1.meta_value, 1) as exchange_rate
					,CONCAT(OM_EXISTING.meta_key, '_base_currency') AS refund_meta_key_base_currency
					,ROUND(OM_EXISTING.meta_value *
								 COALESCE(EXCHANGE_RATES.exchange_rate,
												 (COALESCE(ORDER_META2.meta_value, 0) / COALESCE(ORDER_META1.meta_value, 1))),
								 %d) AS meta_value_base_currency
				FROM
					{$this->wpdb->posts} AS refunds
				-- Get the meta in order currency
				LEFT JOIN
					{$this->wpdb->postmeta} AS OM_EXISTING ON
						(OM_EXISTING.post_id = refunds.ID) AND
						(OM_EXISTING.meta_key IN ('_order_total', '_order_discount', '_cart_discount', '_order_shipping', '_order_tax', '_order_shipping_tax', '_refund_amount'))
				-- Meta from parent orders - START
				JOIN
					{$this->wpdb->posts} AS parent_orders ON
						(parent_orders.ID = refunds.post_parent)
				-- Get order total in order currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META1 ON
						(ORDER_META1.post_id = parent_orders.ID) AND
						(ORDER_META1.meta_key = '_order_total')
				-- Get order total in base currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META2 ON
						(ORDER_META2.post_id = parent_orders.ID) AND
						(ORDER_META2.meta_key = '_order_total_base_currency')
				-- Get order currency
				JOIN
					{$this->wpdb->postmeta} AS ORDER_META3 ON
						(ORDER_META3.post_id = parent_orders.ID) AND
						(ORDER_META3.meta_key = '_order_currency')
				-- Meta from parent orders - END

				-- Get the exchange rate to calculate the refund totals in base currency
				LEFT JOIN
					(
						SELECT
							DATE(FX_ORDERS.post_date) AS order_date
							,FX_ORDERS_META3.meta_value AS order_currency
							,AVG(FX_ORDERS_META2.meta_value / FX_ORDERS_META1.meta_value) AS exchange_rate
						FROM
							{$this->wpdb->posts} AS FX_ORDERS
							-- Get order total in order currency
							JOIN
							{$px}postmeta AS FX_ORDERS_META1 ON
								(FX_ORDERS_META1.post_id = FX_ORDERS.ID) AND
								(FX_ORDERS_META1.meta_key = '_order_total')
							-- Get order total in base currency
							JOIN
								{$px}postmeta AS FX_ORDERS_META2 ON
									(FX_ORDERS_META2.post_id = FX_ORDERS.ID) AND
									(FX_ORDERS_META2.meta_key = '_order_total_base_currency')
							-- Get order total in base currency
							JOIN
								{$px}postmeta AS FX_ORDERS_META3 ON
									(FX_ORDERS_META3.post_id = FX_ORDERS.ID) AND
									(FX_ORDERS_META3.meta_key = '_order_currency')
						GROUP BY
							DATE(FX_ORDERS.post_date)
							,FX_ORDERS_META3.meta_value
					) AS EXCHANGE_RATES ON
					(EXCHANGE_RATES.order_currency = ORDER_META3.meta_value) AND
					(EXCHANGE_RATES.order_date = DATE(refunds.post_date))
				WHERE
					(refunds.post_type = 'shop_order_refund')
			) AS REFUNDS_META;
		", $price_decimals);

		// Debug
		//var_dump($SQL);die();

		$this->add_message(E_USER_NOTICE,
											 __('Recalculating refunds totals in base currency...'));
		$dataset = $this->select($SQL);
		// Debug
		//var_dump($orders_to_update); die();

		$result = true;
		foreach($dataset as $refund_meta) {
			try {
				update_post_meta($refund_meta->refund_id,
												 $refund_meta->refund_meta_key_base_currency,
												 $refund_meta->meta_value_base_currency);
			}
			catch(Exception $e) {
				$this->add_message(E_USER_ERROR,
													 sprintf(__('Exception occurred updating base currency values for refund %s. ' .
																			'Data (JSON): "%s". Error: %s.'),
																	 $refund_meta->refund_id,
																	 json_encode($refund_meta),
																	 $e->getMessage()));
				$result = false;
			}
		}

		// Debug
		//var_dump($order_items_to_update);die();
		if($result === false) {
			$this->add_message(E_USER_ERROR,
												 __('Failed. Please check PHP error log for error messages ' .
														'related to the operation.'));
			return false;
		}
		else {
			$this->add_message(E_USER_NOTICE,
												 sprintf(__('Done. %s rows affected.'), count($dataset)));
		}
		return true;
	}
}
