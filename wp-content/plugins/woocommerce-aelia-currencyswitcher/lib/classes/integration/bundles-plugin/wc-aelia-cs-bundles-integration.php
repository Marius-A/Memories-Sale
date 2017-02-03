<?php if(!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Implements support for WooThemes Bundles plugin.
 */
class WC_Aelia_CS_Bundles_Integration {
	// @var WC_Aelia_CurrencyPrices_Manager The object that handles Currency Prices for the Products.
	private $currencyprices_manager;
	protected static $currency_switcher;

	// @var string The shop's base currency
	protected static $_base_currency;
	// @var string The active currency
	protected static $_selected_currency;

	/**
	 * Returns the instance of the Currency Switcher plugin.
	 *
	 * @return WC_Aelia_CurrencySwitcher
	 */
	protected static function cs() {
		if(empty(self::$currency_switcher)) {
			self::$currency_switcher = WC_Aelia_CurrencySwitcher::instance();
		}
		return self::$currency_switcher;
	}

	public function __construct() {
		$this->currencyprices_manager = WC_Aelia_CurrencyPrices_Manager::Instance();
		$this->set_hooks();
	}

	/**
	 * Set the hooks required by the class.
	 */
	protected function set_hooks() {
		add_filter('wc_aelia_currencyswitcher_product_convert_callback', array($this, 'wc_aelia_currencyswitcher_product_convert_callback'), 10, 2);
		add_action('woocommerce_process_product_meta_bundle', array($this->currencyprices_manager, 'process_product_meta'));
		add_filter('woocommerce_bundle_price_html', array($this, 'woocommerce_bundle_price_html'), 10, 2);
		add_filter('woocommerce_bundle_sale_price_html', array($this, 'woocommerce_bundle_sale_price_html'), 10, 2);

		add_filter('woocommerce_bundle_get_base_price', array($this, 'woocommerce_bundle_get_base_price'), 10, 2);
		add_filter('woocommerce_bundle_get_base_regular_price', array($this, 'woocommerce_bundle_get_base_regular_price'), 10, 2);
		add_filter('woocommerce_bundle_get_base_sale_price', array($this, 'woocommerce_bundle_get_base_sale_price'), 10, 2);
	}

	public static function base_currency() {
		if(empty(self::$_base_currency)) {
			self::$_base_currency = WC_Aelia_CurrencySwitcher::settings()->base_currency();
		}
		return self::$_base_currency;
	}

	public function selected_currency() {
		if(empty(self::$_selected_currency)) {
			self::$_selected_currency = self::cs()->get_selected_currency();
		}
		return self::$_selected_currency;
	}

	/**
	 * Converts all the prices of a given product in the currently selected
	 * currency.
	 *
	 * @param WC_Product product The product whose prices should be converted.
	 * @return WC_Product
	 */
	protected function convert_product_prices($product) {
		$selected_currency = self::selected_currency();
		$base_currency = self::base_currency();

		if(get_value('currency', $product) != $selected_currency) {
			$product = $this->currencyprices_manager->convert_product_prices($product, $selected_currency);
			$product->currency = $selected_currency;
		}

		return $product;
	}

	/**
	 * Converts the price for a bundled product. With bundled products, price
	 * is passed "as-is" and it doesn't get converted into currency.
	 *
	 * @param string bundle_price_html The HTML snippet containing a
	 * bundle's regular price in base currency.
	 * @param WC_Product product The product being displayed.
	 * @return string The HTML snippet with the price converted into currently
	 * selected currency.
	 */
	public function woocommerce_bundle_price_html($bundle_price_html, $product) {
		$product = $this->convert_product_prices($product);

		$bundle_price_html = $product->get_price_html_from_text();
		$bundle_price_html .= woocommerce_price($product->min_bundle_price);
		return $bundle_price_html;
	}

	/**
	 * Converts the price for a bundled Products on sale. With sales, the regular
	 * price is passed "as-is" and it doesn't get converted into currency.
	 *
	 * @param string bundle_sale_price_html The HTML snippet containing a
	 * Product's regular price and sale price.
	 * @param WC_Product product The product being displayed.
	 * @return string The HTML snippet with the sale price converted into
	 * currently selected currency.
	 */
	public function woocommerce_bundle_sale_price_html($bundle_sale_price_html, $product) {
		$product = $this->convert_product_prices($product);

		$min_bundle_regular_price_in_currency = self::cs()->format_price($product->min_bundle_regular_price);
		$min_bundle_sale_price_in_currency = $product->min_bundle_price;
		if($min_bundle_sale_price_in_currency <= 0) {
			$min_bundle_sale_price_in_currency = __('Free!', 'woocommerce');
		} else{
			$min_bundle_sale_price_in_currency = self::cs()->format_price($min_bundle_sale_price_in_currency);
		}

		$bundle_sale_price_html = $product->get_price_html_from_text();
		return '<del>' . $min_bundle_regular_price_in_currency . '</del> <ins>' . $min_bundle_sale_price_in_currency . '</ins>';
	}

	/**
	 * Callback to perform the conversion of bundle prices into selected currencu.
	 *
	 * @param callable $convert_callback A callable, or null.
	 * @param WC_Product The product to examine.
	 * @return callable
	 */
	public function wc_aelia_currencyswitcher_product_convert_callback($convert_callback, $product) {
		$method_keys = array(
			'WC_Product_Bundle' => 'bundle',
		);

		// Determine the conversion method to use
		$method_key = get_value(get_class($product), $method_keys, '');
		$convert_method = 'convert_' . $method_key . '_product_prices';

		if(!method_exists($this, $convert_method)) {
			return $convert_callback;
		}

		return array($this, $convert_method);
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
		if((empty($product->base_sale_price_dates_from) ||
				$today >= date('Ymd', $product->base_sale_price_dates_from)) &&
			 (empty($product->base_sale_price_dates_to) ||
				date('Ymd', $product->base_sale_price_dates_to) > $today)) {
			$sale_price = $product->get_base_sale_price();
			return is_numeric($sale_price) && ($sale_price < $product->get_base_regular_price());
		}
		return false;
	}

	/**
	 * Recalculates bundle's prices, based on selected currency.
	 *
	 * @param WC_Product_Bundle product The bundle whose prices will be converted.
	 */
	protected function convert_bundle_base_prices(WC_Product_Bundle $product, $currency) {
		$shop_base_currency = self::base_currency();
		$product_base_currency = $this->currencyprices_manager->get_product_base_currency($product->id);

		// TODO Load product's base prices in each currency
		$bundle_base_regular_prices_in_currency = array();
		$bundle_base_sale_prices_in_currency = array();

		// Take regular price in the specific product base currency
		$product_base_regular_price = get_value($product_base_currency, $bundle_base_regular_prices_in_currency);
		// If a regular price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_regular_price)) {
			$product_base_regular_price = get_value($shop_base_currency, $bundle_base_regular_prices_in_currency, $product->base_regular_price);
		}

		// Take sale price in the specific product base currency
		$product_base_sale_price = get_value($product_base_currency, $bundle_base_sale_prices_in_currency);
		// If a sale price was not entered for the selected product base currency,
		// take the one in shop base currency
		if(!is_numeric($product_base_sale_price)) {
			$product_base_sale_price = get_value($shop_base_currency, $bundle_base_sale_prices_in_currency, $product->base_sale_price);
		}

		$product->base_regular_price = get_value($currency, $bundle_base_regular_prices_in_currency);
		if(($currency != $product_base_currency) && !is_numeric($product->base_regular_price)) {
			$product->base_regular_price = $this->currencyprices_manager->convert_product_price_from_base($product_base_regular_price, $currency, $product_base_currency, $product);
		}
																				;
		$product->base_sale_price = get_value($currency, $bundle_base_sale_prices_in_currency);
		if(($currency != $product_base_currency) && !is_numeric($product->base_sale_price)) {
			$product->base_sale_price = $this->currencyprices_manager->convert_product_price_from_base($product_base_sale_price, $currency, $product_base_currency, $product);
		}

		// Debug
		//var_dump(
		//	"PRODUCT CLASS: " . get_class($product),
		//	"PRODUCT ID: {$product->id}",
		//	"BASE CURRENCY $product_base_currency",
		//	$bundle_base_regular_prices_in_currency,
		//	$product->regular_price,
		//	$product->sale_price
		//);

		if(!is_numeric($product->base_regular_price) ||
			 $this->product_is_on_sale($product)) {
			$product->base_price = $product->base_sale_price;
		}
		else {
			$product->base_price = $product->base_regular_price;
		}
		return $product;
	}

	/**
	 * Converts the prices of a bundle product to the specified currency.
	 *
	 * @param WC_Product_Bundle product A variable product.
	 * @param string currency A currency code.
	 * @return WC_Product_Bundle The product with converted prices.
	 */
	public function convert_bundle_product_prices(WC_Product_Bundle $product, $currency) {
		$bundled_products = get_value('bundled_products', $product, array());

		if($product->is_priced_per_product()) {
			$this->convert_bundle_base_prices($product, $currency);
		}
		else {
			$product = $this->currencyprices_manager->convert_simple_product_prices($product, $currency);
		}

		return $product;
	}

	public function woocommerce_bundle_get_base_price($price, $product) {
		$product = $this->convert_product_prices($product);
		return $product->base_price;
	}

	public function woocommerce_bundle_get_base_regular_price($price, $product) {
		$product = $this->convert_product_prices($product);
		return $product->base_regular_price;
	}

	public function woocommerce_bundle_get_base_sale_price($price, $product) {
		$product = $this->convert_product_prices($product);
		return $product->base_sale_price;
	}
}
