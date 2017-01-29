<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

if ( !defined( 'ABSPATH' ) ) {
    exit;
} // Exit if accessed directly

if ( !class_exists( 'YITH_WC_Custom_Checklist' ) ) {

    /**
     * Outputs a custom checklist template in plugin options panel
     *
     * @class   YITH_WC_Custom_Checklist
     * @package Yithemes
     * @since   1.0.0
     * @author  Your Inspiration Themes
     *
     */
    class YITH_WC_Custom_Checklist {

        /**
         * Single instance of the class
         *
         * @var \YITH_WC_Custom_Checklist
         * @since 1.0.0
         */
        protected static $instance;

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WC_Custom_Checklist
         * @since 1.0.0
         */
        public static function get_instance() {

            if ( is_null( self::$instance ) ) {

                self::$instance = new self( $_REQUEST );

            }

            return self::$instance;
        }

        /**
         * Constructor
         *
         * @since   1.0.0
         * @return  mixed
         * @author  Alberto Ruggiero
         */
        public function __construct() {

            add_action( 'woocommerce_admin_field_yith-wc-custom-checklist', array( $this, 'output' ) );

        }

        /**
         * Outputs a custom checklist template in plugin options panel
         *
         * @since   1.0.0
         *
         * @param   $option
         *
         * @author  Alberto Ruggiero
         * @return  void
         */
        public function output( $option ) {

            $custom_attributes = array();

            if ( !empty( $option['custom_attributes'] ) && is_array( $option['custom_attributes'] ) ) {
                foreach ( $option['custom_attributes'] as $attribute => $attribute_value ) {
                    $custom_attributes[] = esc_attr( $attribute ) . '="' . esc_attr( $attribute_value ) . '"';
                }
            }

            $option_value = WC_Admin_Settings::get_option( $option['id'], $option['default'] );

            ?>
            <tr valign="top">
                <th scope="row" class="titledesc">
                    <label for="<?php echo esc_attr( $option['id'] ); ?>"><?php echo esc_html( $option['title'] ); ?></label>
                </th>
                <td class="forminp forminp-<?php echo sanitize_title( $option['type'] ) ?>">

                    <div class="ywcc-checklist-div " style="vertical-align: top; margin-bottom: 3px; <?php echo esc_attr( $option['css'] ) ?>" id="<?php echo esc_attr( $option['id'] ); ?>">
                        <input
                            type="hidden"
                            id="<?php echo esc_attr( $option['id'] ); ?>"
                            class="ywcc-values"
                            name="<?php echo esc_attr( $option['id'] ); ?>"
                            value="<?php echo $option_value; ?>"
                            />

                        <div class="ywcc-value-list select2-container-multi">
                            <ul class="select2-choices">

                            </ul>
                            <div class="ywcc-checklist-ajax">
                                <input
                                    type="text"
                                    id="ywcc-new-element-<?php echo esc_attr( $option['id'] ); ?>"
                                    class="ywcc-insert select2-input form-input-tip"
                                    autocomplete="off"
                                    autocorrect="off"
                                    autocapitalize="off"
                                    spellcheck="false"
                                    placeholder="<?php echo esc_attr( $option['placeholder'] ); ?>"
                                    style="margin: 0; width: 100%; border: 1px solid #ccc; border-top: 0 none;" />
                            </div>
                        </div>
                    </div>
                    <span class="description"><?php echo $option['desc']; ?></span>

                </td>
            </tr>
        <?php
        }

    }

    /**
     * Unique access to instance of YITH_WC_Custom_Checklist class
     *
     * @return \YITH_WC_Custom_Checklist
     */
    function YITH_WC_Custom_Checklist() {

        return YITH_WC_Custom_Checklist::get_instance();

    }

    new YITH_WC_Custom_Checklist();

}