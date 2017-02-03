<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

interface IWC_Aelia_CurrencyPrices_Manager {
	public function convert_product_prices(WC_Product $product, $currency);
	public function convert_external_product_prices(WC_Product_External $product, $currency);
	public function convert_grouped_product_prices(WC_Product_Grouped $product, $currency);
	public function convert_legacy_product_prices(WC_Product $product, $currency);
	public function convert_simple_product_prices(WC_Product $product, $currency);
	public function convert_variable_product_prices(WC_Product $product, $currency);
	public function convert_variation_product_prices(WC_Product_Variation $product, $currency);
}

/**
 * Handles currency conversion for the various product types.
 * Due to its architecture, this class should not be instantiated twice. To get
 * the instance of the class, call WC_Aelia_CurrencyPrices_Manager::Instance().
 */
class WC_Aelia_CurrencyPrices_Manager implements IWC_Aelia_CurrencyPrices_Manager {
	protected $admin_views_path;

	// @var WC_Aelia_CurrencyPrices_Manager The singleton instance of the prices manager
	protected static $instance;

	// @var Shop's base currency. Used for caching.
	protected static $_base_currency;

	const FIELD_REGULAR_CURRENCY_PRICES = '_regular_currency_prices';
	const FIELD_SALE_CURRENCY_PRICES = '_sale_currency_prices';
	const FIELD_VARIABLE_REGULAR_CURRENCY_PRICES = 'variable_regular_currency_prices';
	const FIELD_VARIABLE_SALE_CURRENCY_PRICES = 'variable_sale_currency_prices';
	const FIELD_PRODUCT_BASE_CURRENCY = '_product_base_currency';
	const FIELD_COUPON_CURRENCY_DATA = '_coupon_currency_data';

	/**
	 * Convenience method. Returns the instance of the Currency Switcher.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected function currencyswitcher() {
		return WC_Aelia_CurrencySwitcher::instance();
	}

	/**
	 * Convenience method. Returns WooCommerce base currency.
	 *
	 * @return string
	 */
	public function base_currency() {
		if(empty(self::$_base_currency)) {
			self::$_base_currency = WC_Aelia_CurrencySwitcher::settings()->base_currency();
		}
		return self::$_base_currency;
	}

	/**
	 * Returns the active currency.
	 *
	 * @return string The code of currently selected currency.
	 * @since 3.7.9.150813
	 */
	public function get_selected_currency() {
		return $this->currencyswitcher()->get_selected_currency();
	}

	/**
	 * Converts an amount from base currency to another.
	 *
	 * @param float amount The amount to convert.
	 * @param string to_currency The destination Currency.
	 * @param int precision The precision to use when rounding the converted result.
	 * @return float The amount converted in the destination currency.
	 */
	public function convert_from_base($amount, $to_currency, $from_currency = null) {
		// If the amount is not numeric, then it cannot be converted reliably
		// (assuming that it's "zero" would be incorrect
		if(!is_numeric($amount)) {
			return $amount;
		}

		if(empty($from_currency)) {
			$from_currency = $this->base_currency();
		}

		return $this->currencyswitcher()->convert($amount, $from_currency, $to_currency);
	}

	/**
	 * Converts a product price from base currency to another. This method is
	 * equivalent to WC_Aelia_CurrencyPrices_Manager::convert_from_base(), the only
	 * difference is that it triggers an event to allow 3rd parties to alter the
	 * converted amount.
	 *
	 * @param float amount The amount to convert.
	 * @param string to_currency The destination Currency.
	 * @param int precision The precision to use when rounding the converted result.
	 * @param WC_Product The product whose price is being converted.
	 * @param string price_type The price being converted (e.g. regular_price,
	 * sale_price, signup_fee, etc).
	 * @return float The amount converted in the destination currency.
	 * @see WC_Aelia_CurrencyPrices_Manager::convert_from_base()
	 * @since 3.8.11.151107
	 */
	public function convert_product_price_from_base($amount, $to_currency, $from_currency = null, $product = null, $price_type = '') {
		// If the amount is not numeric, then it cannot be converted reliably.
		// Assuming that it's "zero" would be incorrect
		if(!is_numeric($amount)) {
			return $amount;
		}

		// Allow 3rd parties to modify the converted price
		return apply_filters('wc_aelia_cs_convert_product_price',
												 $this->convert_from_base($amount, $to_currency, $from_currency),
												 $amount,
												 $from_currency,
												 $to_currency,
												 WC_Aelia_CurrencySwitcher::settings()->price_decimals($to_currency),
												 $product,
												 $price_type);
	}

	/**
	 * Callback for array_filter(). Returns true if the passed value is numeric.
	 *
	 * @param mixed value The value to check.
	 * @return bool
	 */
	protected function keep_numeric($value) {
		return is_numeric($value);
	}

	/**
	 * Returns the minimum numeric value found in an array. Non numeric values are
	 * ignored. If no numeric value is passed in the array of values, then NULL is
	 * returned.
	 *
	 * @param array values An array of values.
	 * @return float|null
	 */
	public function get_min_value(array $values) {
		$values = array_filter($values, array($this, 'keep_numeric'));

		if(empty($values)) {
			return null;
		}
		return min($values);
	}

	/**
	 * Returns the maximum numeric value found in an array. Non numeric values are
	 * ignored. If no numeric value is passed in the array of values, then NULL is
	 * returned.
	 *
	 * @param array values An array of values.
	 * @return float|null
	 */
	public function get_max_value(array $values) {
		$values = array_filter($values, array($this, 'keep_numeric'));

		if(empty($values)) {
			return null;
		}

		return max($values);
	}

	/*** Hooks ***/
	/**
	 * Display Currency prices for Simple Products.
	 */
	public function woocommerce_product_options_pricing() {
		global $post;
		$this->current_post = $post;

		$file_to_load = apply_filters('wc_aelia_currencyswitcher_simple_product_pricing_view_load', 'simpleproduct_currencyprices_view.php', $post);
		$this->load_view($file_to_load);
	}

	/**
	 * Display Currency prices for Variable Products.
	 */
	public function woocommerce_product_after_variable_attributes($loop, $variation_data, $variation = null) {
		//var_dump($loop, $variation_data, $variation);
		// A Variation instance is not passed by WooCommerce 1.6. In such case, we
		// have to retrieve the variation using its ID.
		if(empty($variation)) {
			$variation_id = get_value('variation_post_id', $variation_data, null);
			if(!empty($variation_id)) {
				$variation = new WC_Product_Variation($variation_id);
				// WooCommerce 1.6 doesn't populate the ID field, therefore we set it
				// manually
				$variation->ID = $variation_id;
			}
			else {
				trigger_error(sprintf(__('Hook "woocommerce_product_after_variable_attributes". Unexpected ' .
																 'condition: variation ID is empty. Variation data (JSON): "%s".'),
											json_encode($variation_data)),
							E_USER_WARNING);
			}
		}
		$this->current_post = $variation;

		$this->loop_idx = $loop;

		$file_to_load = apply_filters('wc_aelia_currencyswitcher_variation_product_pricing_view_load', 'variation_currencyprices_view.php', $variation);
		$this->load_view($file_to_load);
	}

	/**
	 * Event handler fired when a Product is being saved. It processes and saves
	 * the Currency Prices associated with the Product.
	 *
	 * @param int post_id The ID of the Post (product) being saved.
	 */
	public function process_product_meta($post_id) {
		//var_dump($_POST);die();

		$product_regular_prices = $this->sanitise_currency_prices(get_value(self::FIELD_REGULAR_CURRENCY_PRICES, $_POST));
		$product_sale_prices = $this->sanitise_currency_prices(get_value(self::FIELD_SALE_CURRENCY_PRICES, $_POST));

		// D.Zanella - This code saves the product prices in the different Currencies
		update_post_meta($post_id, self::FIELD_REGULAR_CURRENCY_PRICES, json_encode($product_regular_prices));
		update_post_meta($post_id, self::FIELD_SALE_CURRENCY_PRICES, json_encode($product_sale_prices));
		update_post_meta($post_id, self::FIELD_PRODUCT_BASE_CURRENCY, get_value(self::FIELD_PRODUCT_BASE_CURRENCY, $_POST));
	}

	/**
	 * Event handler fired when a Product is being saved. It processes and saves
	 * the Currency Prices associated with the Product.
	 *
	 * @param int post_id The ID of the Post (product) being saved.
	 */
	public function woocommerce_process_product_meta_variable($post_id) {
		global $woocommerce;
		//var_dump($_POST);die();

		// Retrieve all IDs, regular prices and sale prices for all variations. The
		// "all_" prefix has been added to easily distinguish these variables from
		// the ones containing the data of a single variation, whose names would
		// be otherwise very similar
		$all_variations_ids = get_value('variable_post_id', $_POST, array());
		$all_variations_regular_currency_prices = get_value(self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES, $_POST);
		$all_variations_sales_currency_prices = get_value(self::FIELD_VARIABLE_SALE_CURRENCY_PRICES, $_POST);
		$all_variations_base_currencies = get_value(self::FIELD_PRODUCT_BASE_CURRENCY, $_POST);

		foreach($all_variations_ids as $variation_idx => $variation_id) {
			$variation_regular_currency_prices = $this->sanitise_currency_prices(get_value($variation_idx, $all_variations_regular_currency_prices, null));
			$variation_sale_currency_prices = $this->sanitise_currency_prices(get_value($variation_idx, $all_variations_sales_currency_prices, null));

			if(version_compare($woocommerce->version, '2.3', '>=')) {
				// WC 2.3 can handle correctly product base currency on variations
				$variation_base_currency = get_value($variation_idx, $all_variations_base_currencies, $this->base_currency());
			}
			else {
				/**
				 * WooCommerce 2.1 and earlier contain a bug that doesn't allow to set
				 * a product base currency reliably. In these versions, variation base
				 * currency must match WooCommerce base currency. There is no way to set
				 * another base currency, due to current WooCommerce architecture.
				 *
				 * @link https://aelia.freshdesk.com/helpdesk/tickets/1383
				 * @since 3.6.12.140121
				 */
				$variation_base_currency = $this->base_currency();
			}

			// D.Zanella - This code saves the variation prices in the different Currencies
			update_post_meta($variation_id, self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES, json_encode($variation_regular_currency_prices));
			update_post_meta($variation_id, self::FIELD_VARIABLE_SALE_CURRENCY_PRICES, json_encode($variation_sale_currency_prices));
			update_post_meta($variation_id, self::FIELD_PRODUCT_BASE_CURRENCY, $variation_base_currency);
		}
	}

	/**
	 * Handles the saving of variations data using the new logic introduced in
	 * WooCommerce 2.4.
	 *
	 * @param int product_id The ID of the variable product whose variations are
	 * being saved.
	 * @since 3.7.5.150730
	 * @since WC 2.4
	 */
	public function woocommerce_ajax_save_product_variations($product_id) {
		$this->woocommerce_process_product_meta_variable($product_id);
	}

	/**
	 * Handles the bulk edit of variations data using the new logic introduced in
	 * WooCommerce 2.4.
	 *
	 * @param string bulk_action The action to be performed on te variations.
	 * @param mixed data The data passed with the action.
	 * @param int product_id The ID of the variable product whose variations are
	 * being saved.
	 * @param array variations An array of the variations IDs against which the
	 * action is going to be performed.
	 * @since 3.8.5.150907
	 * @since WC 2.4
	 */
	public function woocommerce_bulk_edit_variations($bulk_action, $data, $product_id, $variations) {
		$prices_type = '';
		// Check if the action is to set variations' regular prices
		if(stripos($bulk_action, 'variable_regular_currency_prices') === 0) {
			$prices_type = self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES;
		}
		// Check if the action is to set variations' sale prices
		if(stripos($bulk_action, 'variable_sale_currency_prices') === 0) {
			$prices_type = self::FIELD_VARIABLE_SALE_CURRENCY_PRICES;
		}

		if(!empty($prices_type)) {
			$this->bulk_set_variations_prices($variations, $prices_type, $data['currency'], $data['value']);
		}
	}

	/**
	 * Sets a price for a list of variations.
	 *
	 * @param array variations An array of variations to update.
	 * @param string prices_type The type of price to update.
	 * @param string currency The currency in which the price is being set.
	 * @param float price The price to set.
	 * @since 3.8.5.150907
	 * @since WC 2.4
	 */
	protected function bulk_set_variations_prices($variations, $prices_type, $currency, $price) {
		if(!is_array($variations) || empty($variations)) {
			return;
		}

		foreach($variations as $variation_id) {
			// Retrieve the existing prices
			$prices = $this->get_product_currency_prices($variation_id, $prices_type);
			// Set the new price on the variation
			$prices[$currency] = $price;
			update_post_meta($variation_id, $prices_type, json_encode($prices));
		}
	}

	/**
	 * Returns the HTML to display minimum price for a grouped product, in
	 * currently selected currency. This method replaces the logic of
	 * WC_Product_Grouped::get_price_html() and takes into account exchange rates
	 * and manually entered product prices.
	 *
	 * @param float price The product price.
	 * @param WC_Product_Grouped product The grouped product.
	 * @return string
	 */
	public function woocommerce_grouped_price_html($price, $product) {
		$child_prices = array();

		foreach($product->get_children(false) as $child_id) {
			// Price must be converted to currently selected currency. To do so, a
			// Product must be instantiated, so that we can find out if there are
			// manually entered prices, or if the exchange rate should be used
			$product = $this->get_product($child_id);
			$child_prices[] = $product->get_price();
		}

		$child_prices = array_unique($child_prices);

		if(!empty($child_prices)) {
			$min_price = min($child_prices);
			$max_price = max($child_prices);
		}
		else {
			$min_price = '';
			$max_price = '';
		}

		$price = '';
		if($min_price) {
			$tax_display_mode = get_option('woocommerce_tax_display_shop');
			$get_price_method = 'get_price_' . $tax_display_mode . 'uding_tax';

			if($min_price == $max_price) {
				$display_price = wc_price($product->$get_price_method(1, $min_price));
			}
			else {
				$from = wc_price($product->$get_price_method(1, $min_price));
				$to = wc_price($product->$get_price_method(1, $max_price));
				$display_price = sprintf( _x( '%1$s&ndash;%2$s', 'Price range: from-to', 'woocommerce' ), $from, $to);
			}
			$price .= $display_price . $product->get_price_suffix();
		}
		return $price;
	}

	/**
	 * Processes an array of Currency => Price values, ensuring that they contain
	 * valid data, and returns the sanitised array.
	 *
	 * @param array currency_prices An array of Currency => Price pairs.
	 * @return array
	 */
	public function sanitise_currency_prices($currency_prices) {
		if(!is_array($currency_prices)) {
			return array();
		}

		$result = array();
		foreach($currency_prices as $currency => $price) {
			// To be valid, the Currency must have been enabled in the configuration
			if(!WC_Aelia_CurrencySwitcher::settings()->is_currency_enabled($currency)) {
				continue;
			}

			// To be valid, the Currency must be a number
			if(!is_numeric($price)) {
				continue;
			}

			$result[$currency] = $price;
		}

		return $result;
	}

	/**
	 * Convenience method. Returns an array of the Enabled Currencies.
	 *
	 * @param bool include_base currency Indicates if the base currency should be
	 * included in the result.
	 * @return array
	 */
	public function enabled_currencies($include_base_currency = true) {
		$enabled_currencies = WC_Aelia_CurrencySwitcher::settings()->get_enabled_currencies();
		if(($include_base_currency == false) &&
			 ($key = array_search($this->base_currency(), $enabled_currencies)) !== false) {
			unset($enabled_currencies[$key]);
		}
		return $enabled_currencies;
	}

	/**
	 * Returns an array of Currency => Price values containing the Currency Prices
	 * of the specified type (e.g. Regular, Sale, etc).
	 *
	 * @param int post_id The ID of the Post (product).
	 * @param string prices_type The type of prices to return.
	 * @return array
	 */
	public function get_product_currency_prices($post_id, $prices_type) {
		$result = json_decode(get_post_meta($post_id, $prices_type, true), true);

		if(!is_array($result)) {
			$result = array();
		}

		$prices_type_field_map = array(
			self::FIELD_REGULAR_CURRENCY_PRICES => '_regular_price',
			self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES => '_regular_price',
			self::FIELD_SALE_CURRENCY_PRICES => '_sale_price',
			self::FIELD_VARIABLE_SALE_CURRENCY_PRICES => '_sale_price',
		);
		$prices_type_field_map = apply_filters('wc_aelia_currencyswitcher_prices_type_field_map', $prices_type_field_map);

		// If a price in base currency was not loaded from the metadata added by the
		// Currency Switcher, take the one from the product metadata
		if(!isset($result[$this->base_currency()]) &&
			 isset($prices_type_field_map[$prices_type])) {
			$result[$this->base_currency()] = get_post_meta($post_id, $prices_type_field_map[$prices_type], true);
		}

		$result = apply_filters('wc_aelia_currencyswitcher_product_currency_prices', $result, $post_id, $prices_type);
		return $result;
	}

	/**
	 * Returns an array of Currency => Price values containing the Regular
	 * Currency Prices a Product.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_product_regular_prices($post_id) {
		$prices =  $this->get_product_currency_prices($post_id,
																									self::FIELD_REGULAR_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Sale Currency
	 * Prices a Product.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_product_sale_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_SALE_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Regular
	 * Currency Prices a Product Variation.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_variation_regular_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_VARIABLE_REGULAR_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns an array of Currency => Price values containing the Sale Currency
	 * Prices a Product Variation.
	 *
	 * @param int post_id The ID of the Post (product).
	 * @return array
	 */
	public function get_variation_sale_prices($post_id) {
		$prices = $this->get_product_currency_prices($post_id,
																								 self::FIELD_VARIABLE_SALE_CURRENCY_PRICES);
		return $prices;
	}

	/**
	 * Returns the base currency associated to a product. Prices in such currency
	 * will be used to calculate the prices in other currencies (unless they have
	 * been entered explicitly).
	 *
	 * @param int post_id The product ID.
	 * @return string
	 */
	public function get_product_base_currency($post_id) {
		$result = get_post_meta($post_id, self::FIELD_PRODUCT_BASE_CURRENCY, true);
		if(!$this->currencyswitcher()->is_valid_currency($result)) {
			$result = $this->base_currency();
		}
		return $result;
	}

	/**
	 * Loads (includes) a View file.
	 *
	 * @param string view_file_name The name of the view file to include.
	 */
	private function load_view($view_file_name) {
		$file_to_load = $this->admin_views_path . '/' . $view_file_name;
		$file_to_load = apply_filters('wc_aelia_currencyswitcher_product_pricing_view_load', $file_to_load);

		if(!empty($file_to_load) && is_readable($file_to_load)) {
			include($file_to_load);
		}
	}

	/**
	 * Sets the hooks required by the class.
	 */
	private function set_hooks() {
		// Hooks for simple, external and grouped products
		add_action('woocommerce_product_options_pricing', array($this, 'woocommerce_product_options_pricing'));
		add_action('woocommerce_process_product_meta_simple', array($this, 'process_product_meta'));
		add_action('woocommerce_process_product_meta_external', array($this, 'process_product_meta'));

		// Hooks for variable products
		add_action('woocommerce_product_after_variable_attributes', array($this, 'woocommerce_product_after_variable_attributes'), 5, 3);
		add_action('woocommerce_process_product_meta_variable', array($this, 'woocommerce_process_product_meta_variable'));

		// Hooks for grouped products
		add_action('woocommerce_process_product_meta_grouped', array($this, 'process_product_meta'));
		add_action('woocommerce_grouped_price_html', array($this, 'woocommerce_grouped_price_html'), 10, 2);

		// WooCommerce 2.1+
		add_filter('woocommerce_get_variation_regular_price', array($this, 'woocommerce_get_variation_regular_price'), 20, 4);
		add_filter('woocommerce_get_variation_sale_price', array($this, 'woocommerce_get_variation_sale_price'), 20, 4);
		add_filter('woocommerce_get_variation_price', array($this, 'woocommerce_get_variation_price'), 20, 4);

		// WooCommerce 2.3+
		global $woocommerce;
		if(version_compare($woocommerce->version, '2.3', '>=')) {
			add_filter('woocommerce_product_is_on_sale', array($this, 'woocommerce_product_is_on_sale'), 20, 2);
		}

		// Bulk pricing for variable products
		add_action('woocommerce_variable_product_bulk_edit_actions', array($this, 'woocommerce_variable_product_bulk_edit_actions'));

		// Filters for 3rd party integration
		add_filter('wc_aelia_cs_product_base_currency', array($this, 'wc_aelia_cs_product_base_currency'), 10, 2);

		// WC 2.4+
		add_action('woocommerce_ajax_save_product_variations', array($this, 'woocommerce_ajax_save_product_variations'));
		add_action('woocommerce_bulk_edit_variations', array($this, 'woocommerce_bulk_edit_variations'), 10, 4);
		// Transient keys
		add_filter('woocommerce_get_variation_prices_hash', array($this, 'woocommerce_get_variation_prices_hash'), 10, 3);
		if(version_compare($woocommerce->version, '2.4', '>=')) {
			add_filter('woocommerce_get_children', array($this, 'woocommerce_get_children'), 5, 3);

			// Ensure that the variation prices are the ones in the correct currency.
			// These filters fix the issue caused by the new price caching logic introduced
			// in WooCommerce 2.4.7, which further complicates things (unnecessarily)
			// @since 2.4.7
			add_filter('woocommerce_variation_prices_price', array($this, 'woocommerce_variation_prices_price'), 5, 3);
			add_filter('woocommerce_variation_prices_regular_price', array($this, 'woocommerce_variation_prices_regular_price'), 5, 3);
			add_filter('woocommerce_variation_prices_sale_price', array($this, 'woocommerce_variation_prices_sale_price'), 5, 3);
		}

		// Coupons
		add_action('woocommerce_coupon_options_save', array($this, 'woocommerce_coupon_options_save'), 10, 1);
	}

	/**
	 * Returns the method to be used to convert the prices of a product. The
	 * method depends on the class of the product instance.
	 *
	 * @param WC_Product product An instance of a product.
	 * @return string|null The method to use to process the product, or null if
	 * product type is unsupported.
	 */
	protected function get_convert_callback(WC_Product $product) {
		$method_keys = array(
			'WC_Product' => 'legacy',
			'WC_Product_Simple' => 'simple',
			'WC_Product_Variable' => 'variable',
			'WC_Product_Variation' => 'variation',
			'WC_Product_External' => 'external',
			'WC_Product_Grouped' => 'grouped',
		);

		$method_key = get_value(get_class($product), $method_keys, '');
		// Determine the method that will be used to convert the product prices
		$convert_method = 'convert_' . $method_key . '_product_prices';
		$convert_callback = method_exists($this, $convert_method) ? array($this, $convert_method) : null;

		// Allow external classes to alter the callback, if needed
		$convert_callback = apply_filters('wc_aelia_currencyswitcher_product_convert_callback', $convert_callback, $product);
		if(!is_callable($convert_callback)) {
			trigger_error(sprintf(__('Attempted to convert an unsupported product object. This usually happens when a ' .
															 '3rd party plugin adds custom product types, of which the Currency Switcher is ' .
															 'not aware. Product prices will not be converted. Please report the issue to ' .
															 'support as a compatibility request. Product type that triggered the message: "%s".'),
														$product->product_type),
										E_USER_NOTICE);
		}
		return $convert_callback;
	}

	/**
	 * Indicates if the product is on sale. A product is considered on sale if:
	 * - Its "sale end date" is empty, or later than today.
	 * - Its sale price in the active currency is lower than its regular price.
	 *
	 * @param WC_Product product The product to check.
	 * @return bool
	 */
	protected function product_is_on_sale(WC_Product $product) {
		$today = date('Ymd');
		if((empty($product->sale_price_dates_from) ||
				$today >= date('Ymd', $product->sale_price_dates_from)) &&
			 (empty($product->sale_price_dates_to) ||
				date('Ymd', $product->sale_price_dates_to) > $today)) {
			$sale_price = $product->get_sale_price();
			return is_numeric($sale_price) && ($sale_price < $product->get_regular_price());
		}
		return false;
	}

	/**
	 * Converts a product or variation prices to the specific currency, taking
	 * into account manually entered prices.
	 *
	 * @param WC_Product product The product whose prices should be converted.
	 * @param string currency A currency code.
	 * @param array product_regular_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @param array product_sale_prices_in_currency An array of manually entered
	 * product prices (one for each currency).
	 * @return WC_Product
	 */
	protected function convert_to_currency(WC_Product $product, $currency,
																				 array $product_regular_prices_in_currency,
																				 array $product_sale_prices_in_currency) {
		$shop_base_currency = $this->base_currency();
		$product_base_currency = $this->get_product_base_currency($product->id);

		// Take regular price in the specific product base currency
		$product_base_regular_price = get_value($product_base_currency, $product_regular_prices_in_currency);
		// If a regular price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_regular_price)) {
			$product_base_regular_price = get_value($shop_base_currency, $product_regular_prices_in_currency);

			// If a product doesn't have a price in the product-specific base currency,
			// then that base currency is not valid. In such case, shop's base currency
			// should be used instead
			$product_base_currency = $shop_base_currency;
		}

		// Take sale price in the specific product base currency
		$product_base_sale_price = get_value($product_base_currency, $product_sale_prices_in_currency);
		// If a sale price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_sale_price)) {
			$product_base_sale_price = get_value($shop_base_currency, $product_sale_prices_in_currency);
		}

		$product->regular_price = get_value($currency, $product_regular_prices_in_currency);
		if(($currency != $product_base_currency) && !is_numeric($product->regular_price)) {
			$product->regular_price = $this->convert_product_price_from_base($product_base_regular_price, $currency, $product_base_currency, $product, 'regular_price');
		}
																				;
		$product->sale_price = get_value($currency, $product_sale_prices_in_currency);
		if(($currency != $product_base_currency) && !is_numeric($product->sale_price)) {
			$product->sale_price = $this->convert_product_price_from_base($product_base_sale_price, $currency, $product_base_currency, $product, 'sale_price');
		}

		// Debug
		//var_dump(
		//	"PRODUCT CLASS: " . get_class($product),
		//	"PRODUCT ID: {$product->id}",
		//	"BASE CURRENCY $product_base_currency",
		//	$product_regular_prices_in_currency,
		//	$product->regular_price,
		//	$product->sale_price
		//);

		if(!is_numeric($product->regular_price) ||
			 $this->product_is_on_sale($product)) {
			$product->price = $product->sale_price;
		}
		else {
			$product->price = $product->regular_price;
		}
		return $product;
	}

	/**
	 * Convert the prices of a product in the destination currency.
	 *
	 * @param WC_Product product A product (simple, variable, variation).
	 * @param string currency A currency code.
	 * @return WC_Product The product with converted prices.
	 */
	public function convert_product_prices(WC_Product $product, $currency) {
		// Since WooCommerce 2.1, this method can be triggered recursively due to
		// a (not so wise) change in WC architecture. It's therefore necessary to keep
		// track of when the conversion started, to prevent infinite recursion
		if($product->aelia_cs_conversion_in_progress) {
			return $product;
		}

		// If product has a "currencyswitcher_original_product" attribute, it means
		// that it was already processed by the Currency Switcher. In such case, it
		// has to be reverted to the original status before being processed again
		if(!empty($product->currencyswitcher_original_product)) {
			$product = $product->currencyswitcher_original_product;
		}
		// Take a copy of the original product before processing
		$original_product = clone $product;

		// Flag the product to keep track that conversion is in progress
		$product->aelia_cs_conversion_in_progress = true;

		// Get the method to use to process the product
		$convert_callback = $this->get_convert_callback($product);
		if(!empty($convert_callback) && is_callable($convert_callback)) {
			// Invoke the callback directly, rather than using call_user_func(), for
			// better performance
			// @since 3.8.11.151028
			if(is_array($convert_callback)) {
				$object = array_shift($convert_callback);
				$method = array_shift($convert_callback);
				$product = $object->$method($product, $currency);
			}
			else {
				$product = $convert_callback($product, $currency);
			}
			//$product = call_user_func($convert_callback, $product, $currency);
		}
		else {
			// If no conversion function is found, use the generic one
			$product = $this->convert_generic_product_prices($product, $currency);
		}

		// Assign the original product to the processed one
		$product->currencyswitcher_original_product = $original_product;
		// Remove "conversion is in progress" flag from the original product, in case
		// it was left there
		if(!empty($product->currencyswitcher_original_product->aelia_cs_conversion_in_progress)) {
			unset($product->currencyswitcher_original_product->aelia_cs_conversion_in_progress);
		}

		// Remove "conversion is in progress" flag when the operation is complete
		unset($product->aelia_cs_conversion_in_progress);

		return $product;
	}

	/**
	 * Converts the prices of a variable product to the specified currency.
	 *
	 * @param WC_Product_Variable product A variable product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The product with converted prices.
	 */
	public function convert_variable_product_prices(WC_Product $product, $currency) {
		$product_children = $product->get_children(false);
		if(empty($product_children)) {
			return $product;
		}

		$variation_regular_prices = array();
		$variation_sale_prices = array();
		$variation_prices = array();

		foreach($product_children as $variation_id) {
			$variation = $this->load_variation_in_currency($variation_id, $currency);
			if(empty($variation)) {
				continue;
			}

			$variation_regular_prices[$variation_id] = $variation->regular_price;
			$variation_sale_prices[$variation_id] = $variation->sale_price;
			$variation_prices[$variation_id] = $variation->price;
		}

		$product->min_variation_regular_price = $this->get_min_value($variation_regular_prices);
		$product->max_variation_regular_price = $this->get_max_value($variation_regular_prices);

		$product->min_variation_sale_price = $this->get_min_value($variation_sale_prices);
		$product->max_variation_sale_price = $this->get_max_value($variation_sale_prices);

		$product->min_variation_price = $this->get_min_value($variation_prices);
		$product->max_variation_price = $this->get_max_value($variation_prices);

		// Keep track of the variation IDs from which the minimum and maximum prices
		// were taken
		$product->min_regular_price_variation_id = array_search($product->min_variation_regular_price, $variation_regular_prices);
		$product->max_regular_price_variation_id = array_search($product->max_variation_regular_price, $variation_regular_prices);

		$product->min_sale_price_variation_id = array_search($product->min_variation_sale_price, $variation_sale_prices);
		$product->max_sale_price_variation_id = array_search($product->max_variation_sale_price, $variation_sale_prices);

		$product->min_price_variation_id = array_search($product->min_variation_price, $variation_prices);
		$product->max_price_variation_id = array_search($product->max_variation_price, $variation_prices);

		$product->regular_price = $product->min_variation_regular_price;
		$product->sale_price = $product->min_variation_price;
		$product->price = $product->min_variation_price;

		return $product;
	}

	/**
	 * Converts the product prices of a variation.
	 *
	 * @param WC_Product_Variation $product A product variation.
	 * @param string currency A currency code.
	 * @return WC_Product_Variation The variation with converted prices.
	 */
	public function convert_variation_product_prices(WC_Product_Variation $product, $currency) {
		$original_product_id = $product->id;
		$product->id = $product->variation_id;
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->get_variation_regular_prices($product->variation_id),
																					$this->get_variation_sale_prices($product->variation_id));
		$product->id = $original_product_id;

		return $product;
	}

	/**
	 * Given a Variation ID, it loads the variation and returns it, with its
	 * prices converted into the specified currency.
	 *
	 * @param int variation_id The ID of the variation.
	 * @param string currency A currency code.-
	 * @return WC_Product_Variation
	 */
	public function load_variation_in_currency($variation_id, $currency) {
		$variation = new WC_Product_Variation($variation_id);

		if(empty($variation)) {
			return false;
		}
		$variation = $this->convert_variation_product_prices($variation, $currency);
		return $variation;
	}

	/**
	 * Converts the prices of a generic product to the specified currency. This
	 * method is a fallback, in case no specific conversion function was found by
	 * the pricing manager.
	 *
	 * @param WC_Product product A simple product.
	 * @param string currency A currency code.
	 * @return WC_Product The simple product with converted prices.
	 */
	public function convert_generic_product_prices(WC_Product $product, $currency) {
		return $this->convert_simple_product_prices($product, $currency);
	}

	/**
	 * Converts the prices of a simple product to the specified currency.
	 *
	 * @param WC_Product product A simple product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The simple product with converted prices.
	 */
	public function convert_simple_product_prices(WC_Product $product, $currency) {
		$product = $this->convert_to_currency($product,
																					$currency,
																					$this->get_product_regular_prices($product->id),
																					$this->get_product_sale_prices($product->id));

		return $product;
	}

	/**
	 * Converts the prices of an external product to the specified currency.
	 *
	 * @param WC_Product_External product An external product.
	 * @param string currency A currency code.
	 * @return WC_Product_Variable The external product with converted prices.
	 */
	public function convert_external_product_prices(WC_Product_External $product, $currency) {
		return $this->convert_simple_product_prices($product, $currency);
	}

	/**
	 * Converts the prices of a grouped product to the specified currency.
	 *
	 * @param WC_Product_Grouped product A grouped product.
	 * @param string currency A currency code.
	 * @return WC_Product_Grouped
	 */
	public function convert_grouped_product_prices(WC_Product_Grouped $product, $currency) {
		// Grouped products don't have a price. Prices can be found in child products
		// which belong to the grouped product. Such child products are processed
		// independently, therefore no further action is needed
		return $product;
	}

	/**
	 * For WooCommerce 1.6 only.
	 * Converts the prices of a product into the selected currency. This method
	 * is implemented because WC 1.6 used WC_Product class for both simple and
	 * variable products.
	 *
	 * @param WC_Product product A product.
	 * @param string currency A currency code.
	 * @return WC_Product
	 */
	public function convert_legacy_product_prices(WC_Product $product, $currency) {
		$product_children = $product->get_children(false);

		if(empty($product_children)) {
			$product = $this->convert_to_currency($product,
																				$currency,
																				$this->get_product_regular_prices($product->id),
																				$this->get_product_sale_prices($product->id));
		}
		else {
			$product = $this->convert_variable_product_prices($product, $currency);
		}

		return $product;
	}

	/**
	 * Checks that the price type specified is "min" or "max".
	 *
	 * @param string price_type The price type.
	 * @return bool
	 */
	protected function is_min_max_price_type_valid($price_type) {
		$valid_price_types = array(
			'min',
			'max'
		);

		return in_array($price_type, $valid_price_types);
	}

	/**
	 * Process a variation price, recalculating it depending if it already
	 * includes taxes and/or if prices should be displayed with our without taxes.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 * @since 3.2
	 */
	public function process_product_price_tax($product, $price) {
		$tax_display_mode = get_option('woocommerce_tax_display_shop');
		if($tax_display_mode == 'incl') {
			$price = $product->get_price_including_tax(1, $price);
		}
		else {
			$price = $product->get_price_excluding_tax(1, $price);
		}

		return $price;
	}

	/**
	 * Process a variation price, recalculating it depending if it already
	 * includes taxes and/or if prices should be displayed with our without taxes.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 * @since 3.2
	 */
	public function process_variation_price_tax($price, $product, $min_or_max, $display) {
		if($display) {
			$field_name = $min_or_max . '_price_variation_id';
			$variation_id = $product->$field_name;
			$variation = $product->get_child($variation_id);

			$tax_display_mode = get_option('woocommerce_tax_display_shop');
			if($tax_display_mode == 'incl') {
				$price = $variation->get_price_including_tax(1, $price);
			}
			else {
				$price = $variation->get_price_excluding_tax(1, $price);
			}
		}
		return $price;
	}

	/**
	 * Get the minimum or maximum variation regular price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_regular_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax()) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation regular price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$price_property = $min_or_max . '_variation_regular_price';
		// Process the price, recalculating it depending if it already includes tax or not
		$price = $this->process_variation_price_tax($product->$price_property, $product, $min_or_max, $display);

		return $price;
	}

	/**
	 * Get the minimum or maximum variation sale price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_sale_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax()) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation sale price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$sale_price_property = $min_or_max . '_variation_sale_price';
		// Process the price, recalculating it depending if it already includes tax or not
		$sale_price = $this->process_variation_price_tax($product->$sale_price_property, $product, $min_or_max, $display);

		return $sale_price;
	}

	/**
	 * Get the minimum or maximum variation price.
	 *
	 * @param string price The product price passed by WooCommerce.
	 * @param WC_Product product The product for which the price is being retrieved.
	 * @param string min_or_max The type of price to retrieve. It can be 'min' or 'max'.
	 * @param boolean display Whether the value is going to be displayed
	 * @return float
	 */
	public function woocommerce_get_variation_price($price, $product, $min_or_max, $display) {
		// If we are in the backend, no conversion takes place, therefore we can return
		// the original value, in base currency
		if(is_admin() && !WC_Aelia_CurrencySwitcher::doing_ajax()) {
			return $price;
		}

		if(!$this->is_min_max_price_type_valid($min_or_max)) {
			trigger_error(sprintf(__('Invalid variation sale price type specified: "%s".'),
														$min_or_max),
										E_USER_WARNING);
			return $price;
		}

		// Retrieve the price in the selected currency
		$price_property = $min_or_max . '_variation_price';

		//var_dump($price, $product->min_price_variation_id);die();
		// Process the price, recalculating it depending if it already includes tax or not
		$price = $this->process_variation_price_tax($product->$price_property, $product, $min_or_max, $display);

		return $price;
	}

	/**
	 * Indicates if a product is on sale. The method takes into account the product
	 * prices in each currency.
	 *
	 * @param bool is_on_sale The original value passed by WooCommerce.
	 * @param WC_Product product The product to check.
	 * @return bool
	 * @since 3.6.21.140227
	 */
	public function woocommerce_product_is_on_sale($is_on_sale, $product) {
		return $this->product_is_on_sale($product);
	}

	/**
	 * Alters the bulk edit actions for the current product.
	 */
	public function woocommerce_variable_product_bulk_edit_actions() {
		$enabled_currencies = $this->enabled_currencies();
		if(empty($enabled_currencies)) {
			return;
		}

		$text_domain = WC_Aelia_CurrencySwitcher::$text_domain;
		echo '<optgroup label="' . __('Currency prices', $text_domain) . '">';
		foreach($enabled_currencies as $currency) {
			// No need to add an option for the base currency, it already exists in standard WooCommerce menu
			if($currency == $this->base_currency()) {
				continue;
			}

			// Display entry for variation's regular prices
			echo "<option value=\"variable_regular_currency_prices_{$currency}\" currency=\"{$currency}\">";
			printf(__('Regular prices (%s)', $text_domain),
						 $currency);
			echo '</option>';

			// Display entry for variation's sale prices
			echo "<option value=\"variable_sale_currency_prices_{$currency}\"  currency=\"{$currency}\">";
			printf(__('Sale prices (%s)', $text_domain),
						 $currency);
			echo '</option>';
		}
		echo '</optgroup>';
	}

	/**
	 * Class constructor.
	 */
	public function __construct() {
		$this->admin_views_path = AELIA_CS_VIEWS_PATH . '/admin/wc20';
		$this->set_hooks();
	}

	/**
	 * Returns the singleton instance of the prices manager.
	 *
	 * @return WC_Aelia_CurrencyPrices_Manager
	 */
	public static function Instance() {
		if(empty(self::$instance)) {
			self::$instance = new WC_Aelia_CurrencyPrices_Manager();
		}

		return self::$instance;
	}

	/**
	 * Filter for wc_aelia_cs_product_base_currency hook. Returns the base currency
	 * associated to a product.
	 *
	 * @param string product_base_currency The currency passed when the filter was
	 * called.
	 * @return string The product base currency, or shop's base currency if none
	 * was assigned to the product.
	 * @since 3.7.8.150810
	 */
	public function wc_aelia_cs_product_base_currency($product_base_currency, $product_id) {
		return $this->get_product_base_currency($product_id);
	}

	/**
	 * Alters the transient key to retrieve the prices of variable products,
	 * ensuring that the currency is taken into account.
	 *
	 * @param array cache_key_args The arguments that form the cache key.
	 * @param WC_Product product The product for which the key is being generated.
	 * @param bool display Indicates if the prices are being retrieved for display
	 * purposes.
	 * @return array
	 * @since WooCommerce 2.4+
	 * @since 3.7.8.150810
	 */
	public function woocommerce_get_variation_prices_hash($cache_key_args, $product, $display) {
		// Debug
		//$cache_key = 'wc_var_prices' . md5(json_encode($cache_key_args));
		//delete_transient($cache_key);

		$cache_key_args[] = get_woocommerce_currency();
		return $cache_key_args;
	}

	/**
	 * Ensures that the price of a variation being stored in the cache is the one
	 * in the active currency.
	 *
	 * WHY
	 * In WooCommerce 2.4.7, the already convoluted price caching logic has been
	 * made more complicated. The latest implementation loads variation prices
	 * directly from the database (bad idea), skipping all filters that are
	 * associated to prices. This causes the wrong prices to be loaded, as any
	 * calculation is skipped. This filter fixes the issue, by replacing the wrong
	 * prices with the correct ones.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @since 3.8.6.150911
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_price($price, $variation, $parent_product) {
		return $variation->get_price();
	}

	/**
	 * Ensures that the regular price of a variation being stored in the cache is
	 * the one in the active currency.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @see WC_Aelia_CurrencyPrices_Manager::woocommerce_variation_prices_price()
	 * @since 3.8.6.150911
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_regular_price($price, $variation, $parent_product) {
		return $variation->get_regular_price();
	}

	/**
	 * Ensures that the sale price of a variation being stored in the cache is the
	 * one in the active currency.
	 *
	 * @param float price The original variation price, retrieved by WC from the
	 * database.
	 * @param WC_Product_Variation variation The variation for which the price is retrieved.
	 * @param WC_Product_Variable The parent product to which the variation belongs.
	 * @return float The variation price, in the active currency.
	 *
	 * @see WC_Aelia_CurrencyPrices_Manager::woocommerce_variation_prices_price()
	 * @since 3.8.6.150911
	 * @since WC 2.4.7
	 */
	public function woocommerce_variation_prices_sale_price($price, $variation, $parent_product) {
		return $variation->get_sale_price();
	}

	/**
	 * WooCommerce 2.4+
	 * Intercepts the result of WC_Product_Variable::get_children(), ensuring that
	 * all the necessary children are displayed.
	 *
	 * WHY
	 * WooCommerce team performed an over-optimisation of the code, and changed the
	 * query that retrieves a variable product's children so that it discards all
	 * products that have an empty "_price" metadata. In a multi-currency environment,
	 * that metadata represents the price in base currency, and it may easily be
	 * empty, as the Currency Switcher can calculate it on the fly, based on exchange
	 * rates. The content of that meta field must be ignored, and the products
	 * must be filtered after having been retrieved.
	 *
	 * @param array children_products An array of product IDs.
	 * @param WC_Product parent_product The product to which the children belong.
	 * @param bool visible_only Indicates if only visible products should be retrieved.
	 * @return array An array of product IDs.
	 * @since 3.8.1.150813
	 * @link https://github.com/woothemes/woocommerce/issues/8820
	 */
	public function woocommerce_get_children($children_products, $parent_product, $visible_only = false) {
		// @var Semaphore, to prevent the call to $product->get_children(), below,
		// from causing infinite recursion
		static $processing = false;
		if(!$processing) {
			$processing = true;
			// Debug
			//var_dump($children_products);die();

			$children_transient_key = 'wc_product_children_' . $parent_product->id;
			/* Delete the transient key, to force the retrieval of all children.
			 * This is NOT optimal, but we have to use this trick until the logic used
			 * to retrieve variable product's children is updated.
			 */
			delete_transient($children_transient_key);

			$children_products = $parent_product->get_children(false);
			if($visible_only) {
				$hide_out_of_stock = (get_option('woocommerce_hide_out_of_stock_items') === 'yes');
				foreach($children_products as $key => $child_id) {
					// Remove out of stock variations
					if($hide_out_of_stock && get_post_meta($child_id, '_stock_status', true) != 'instock') {
						unset($children_products[$key]);
					}
				}
			}
			$processing = false;
		}
		return $children_products;
	}

	/**
	 * Saves the multi-currency data for a coupon.
	 *
	 * @param int coupon_id The coupon ID.
	 * @since 3.8.0.150813
	 */
	public function woocommerce_coupon_options_save($coupon_id) {
		// Debug
		//var_dump($_POST);die();
		$coupon_currency_data = get_value(self::FIELD_COUPON_CURRENCY_DATA, $_POST, array());
		update_post_meta($coupon_id, self::FIELD_COUPON_CURRENCY_DATA, $coupon_currency_data);
	}

	/**
	 * Replaces the amounts of a coupon with the one applicable for the active
	 * currency. The conversion takes into account values that might have been
	 * explicitly set for the active currency, and applies FX conversion for the
	 * ones set to "Auto".
	 *
	 * @param WC_Coupon coupon The coupon to process.
	 * @since 3.8.0.150813
	 */
	public function set_coupon_amounts($coupon) {
		// If we are working with base currency, do not perform any conversion (it
		// would not make any difference, anyway)
		if($this->get_selected_currency() == $this->base_currency()) {
			return;
		}

		$coupon_types_to_convert = array('fixed_cart', 'fixed_product');
		$coupon_types_to_convert = apply_filters('wc_aelia_cs_coupon_types_to_convert', $coupon_types_to_convert);

		$coupon_data = get_post_meta($coupon->id, WC_Aelia_CurrencyPrices_Manager::FIELD_COUPON_CURRENCY_DATA, true);
		$currency_data = get_value($this->get_selected_currency(), $coupon_data, array());

		/* When a different value is explicitly specified for the active currency,
		 * that will replace the coupon amount. If no value has been specified for
		 * the active currency, then a default one has to be used, as follows:
		 * - If the coupon is a "fixed price" one, then its default amount is the
		 *   original value, converted to the active currency.
		 * - If the coupon is a percentage one, then its default amount is the same
		 *   entered for the base currency.
		 *
		 * Example
		 * When active currency is EUR, the above will work as follows:
		 * - Coupon value for USD: 100 -> Value in EUR: 89.95
		 * - Coupon value for USD: 10% -> Value in EUR: still 10% (no conversion)
		 * - Coupon value for USD: 100, for EUR: 90 -> Value in EUR: 90 (i.e. explicit
		 *   coupon value takes precedence).
		 */
		if(in_array($coupon->discount_type, $coupon_types_to_convert)) {
			$default_coupon_amount = $this->convert_from_base($coupon->coupon_amount, $this->get_selected_currency());
		}
		else {
			$default_coupon_amount = $coupon->coupon_amount;
		}

		$coupon->coupon_amount = get_value('coupon_amount', $currency_data);
		if(!is_numeric($coupon->coupon_amount)) {
			$coupon->coupon_amount = $default_coupon_amount;
		}

		/* Since WooCommerce 2.3, the "amount" property, which is an alias of
		 * coupon_amount, should no longer be used. However, since we deal with
		 * 2.2 and 2.1 as well, it's a good idea to keep it up to date.
		 * @link http://docs.woothemes.com/wc-apidocs/class-WC_Coupon.html
		 */
		$coupon->amount = $coupon->coupon_amount;

		// Debug
		//var_dump($coupon); die();

		// Convert minimum amount to the selected currency
		$min_amount = get_value('minimum_amount', $currency_data);
		if(is_numeric($min_amount)) {
			$coupon->minimum_amount = $min_amount;
		}
		else{
			// If no minimum amount was explicitly specified for the active currency,
			// but there is a minimum amount for the base currency, convert that
			// value using FX rates
			$coupon->minimum_amount = $this->convert_from_base($coupon->minimum_amount, $this->get_selected_currency());
		}

		// Convert maximum amount to the selected currency
		$max_amount = get_value('maximum_amount', $currency_data);
		if(is_numeric($max_amount)) {
			$coupon->maximum_amount = $max_amount;
		}
		else{
			// If no maximum amount was explicitly specified for the active currency,
			// but there is a maximum amount for the base currency, convert that
			// value using FX rates
			$coupon->maximum_amount = $this->convert_from_base($coupon->maximum_amount, $this->get_selected_currency());
		}
	}

	/**
	 * Returns the instance of a product.
	 *
	 * @param int product_id A product ID.
	 * @return WC_Product
	 * @since 3.8.14.151214
	 */
	protected function get_product($product_id) {
		global $woocommerce;
		if(version_compare($woocommerce->version, '2.2', '>=')) {
		//if(aelia_wc_version_is('>=', '2.2')) {
			$product = wc_get_product($product_id);
		}
		else {
			$product = get_product($product_id);
		}
		return $product;
	}
}
