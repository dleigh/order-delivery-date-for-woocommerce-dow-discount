<?php
/**
 * Order Delivery Date for WooCommerce Day of Week Discount
 *
 * Settings added for the plugin in the admin
 *
 * @author   David Leigh
 * @package  Order-Delivery-Date-for-WooCommerce-DOW-Discount/Admin/Settings
 * @since    1.0
 */

/**
 * Class for adding the settings of the plugin in admin.
 */
class Woo_Dowd_Settings {

	/**
	 * Add settings field on Day of Week Discount Settings tab.
	 *
	 * @globals array $woo_dowd_weekdays Weekdays array
	 * @hook admin_init
	 * @since 1.0
	 */
	public static function woo_dowd_dow_discount_admin_settings() {
		global $woo_dowd_weekdays;
		// First, we register a section. This is necessary since all future options must belong to one.
		add_settings_section(
			'woo_dowd_date_settings_section',                               // ID used to identify this section and with which to register options.
			__( 'Day of Week Discount Settings', 'woo-dowd-delivery-day-discount' ),   // Title to be displayed on the administration page.
			array( 'woo_dowd_settings', 'woo_dowd_dow_discount_setting' ),  // Callback used to render the description of the section.
			'orddd_lite_date_settings_page'                                 // Page on which to add this section of options (Order Delivery Date plugin).
		);

		add_settings_field(
			'woo_dowd_enable_dow_discount',
			__( 'Enable Day of Week Discount:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_enable_dow_discount_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array( __( 'Enable the shipping discount based on the day of week and the settings below.', 'woo-dowd-delivery-day-discount' ) )
		);

		add_settings_field(
			'woo_dowd_free_text',
			__( 'Text for calculated Free Shipping:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_free_text_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array( '&nbsp;' . __( 'Enter text to show when discount calculates to Free Shipping.', 'woo-dowd-delivery-day-discount' ) )
		);

		add_settings_field(
			'woo_dowd_percentage_text',
			__( 'Text for Percentage Discount Shipping:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_percentage_text_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array( '&nbsp;' . __( 'Enter text to show when discount is a percentage. Use "@amt@" to display the actual percent.', 'woo-dowd-delivery-day-discount' ) )
		);

		add_settings_field(
			'woo_dowd_amount_text',
			__( 'Text for Shipping discounted by an Amount:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_amount_text_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array( '&nbsp;' . __( 'Enter text to show when discount is an amount. Use "@amt@" to display the actual amount.', 'woo-dowd-delivery-day-discount' ) )
		);

		// This is for the product inclusion/exclusion options.  We add the "field" as the group of fields but with no extra text around it.
		add_settings_field(
			'woo_dowd_dow_inclusion_exclusion',
			__( 'Product Inclusion/Exclusion:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_dow_inclusion_exclusion_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array()
		);

		// For the inclusion/exclusion options, we don't add individual fields, but we do register the settings.
		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_product_ids'
		);
		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_product_ids_in_ex'
		);
		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_product_categories'
		);
		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_product_categories_in_ex'
		);

		// This is for the date table.  We add the "field" as the whole table but with no extra text around it.
		add_settings_field(
			'woo_dowd_dow_discount_table',
			__( 'Day of Week Discount Table:', 'woo-dowd-delivery-day-discount' ),
			array( 'woo_dowd_settings', 'woo_dowd_dow_discount_table_callback' ),
			'orddd_lite_date_settings_page',
			'woo_dowd_date_settings_section',
			array()
		);

		// For the date table, we don't add individual fields, but we do register the settings.
		foreach ( $woo_dowd_weekdays as $n => $day_name ) {
			register_setting(
				'orddd_lite_date_settings',
				$n . '_date_start'
			);
			register_setting(
				'orddd_lite_date_settings',
				$n . '_date_end'
			);
			register_setting(
				'orddd_lite_date_settings',
				$n . '_discount_type'
			);
			register_setting(
				'orddd_lite_date_settings',
				$n . '_discount_amount'
			);
		}

		// And then we register all the rest of the fields for which we created "settings fields".
		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_enable_dow_discount'
		);

		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_free_text'
		);

		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_percentage_text'
		);

		register_setting(
			'orddd_lite_date_settings',
			'woo_dowd_amount_text'
		);

	}

	/**
	 * Callback for Day of Week Discount Settings section
	 *
	 * @since 1.0
	 */
	public static function woo_dowd_dow_discount_setting() { }

	/**
	 * Callback for adding Day of Week Discount checkbox
	 *
	 * @param array $args Callback arguments.
	 * @since 1.0
	 */
	public static function woo_dowd_enable_dow_discount_callback( $args ) {
		$enable_dow_discount = '';
		if ( get_option( 'woo_dowd_enable_dow_discount' ) === 'on' ) {
			$enable_dow_discount = 'checked';
		}

		$html = '<input type="checkbox" name="woo_dowd_enable_dow_discount" id="woo_dowd_enable_dow_discount" class="day-checkbox" value="on" ' . $enable_dow_discount . ' />';

		$html .= '<label for="woo_dowd_enable_dow_discount"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding text for calculated Free Shipping text field
	 *
	 * @param array $args Callback arguments.
	 * @since 1.0
	 */
	public static function woo_dowd_free_text_callback( $args ) {
		$html  = '<input type="text" name="woo_dowd_free_text" id="woo_dowd_free_text" value="' . get_option( 'woo_dowd_free_text' ) . '"/>';
		$html .= '<label for="woo_dowd_free_text"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for the text to display when the discount is a percentage text field
	 *
	 * @param array $args Callback arguments.
	 * @since 1.0
	 */
	public static function woo_dowd_percentage_text_callback( $args ) {
		$html  = '<input type="text" name="woo_dowd_percentage_text" id="woo_dowd_percentage_text" value="' . get_option( 'woo_dowd_percentage_text' ) . '"/>';
		$html .= '<label for="woo_dowd_percentage_text"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for the text to display when the discount is an amount text field
	 *
	 * @param array $args Callback arguments.
	 * @since 1.0
	 */
	public static function woo_dowd_amount_text_callback( $args ) {
		$html  = '<input type="text" name="woo_dowd_amount_text" id="woo_dowd_amount_text" value="' . get_option( 'woo_dowd_amount_text' ) . '"/>';
		$html .= '<label for="woo_dowd_amount_text"> ' . $args[0] . '</label>';
		echo $html;
	}

	/**
	 * Callback for adding the product/product catagory inclusion/exclusion table.
	 *
	 * Here we deal with retrieving and setting the following options:
	 * woo_dowd_product_ids
	 * woo_dowd_product_ids_in_ex
	 * woo_dowd_product_categories
	 * woo_dowd_product_categories_in_ex
	 *
	 * @param array $args Callback arguments.
	 * @since 1.1
	 */
	public static function woo_dowd_dow_inclusion_exclusion_callback( $args ) {
		if ( 'include' === get_option( 'woo_dowd_product_ids_in_ex' ) ) {
			$ids_include = 'checked';
			$ids_exclude = '';
		} else {
			$ids_include = '';
			$ids_exclude = 'checked';
		}

		if ( 'include' === get_option( 'woo_dowd_product_categories_in_ex' ) ) {
			$categories_include = 'checked';
			$categories_exclude = '';
		} else {
			$categories_include = '';
			$categories_exclude = 'checked';
		}

		$html  = '';
		$html .= '<fieldset class="woo-dowd-inclusion_exclusion-fieldset">';
		$html .= '    <table class="woo_dowd_inclusion_exclusion_table">';
		$html .= '     <tbody>';
		$html .= '<tr>
						<td class="woo_dowd_product_ids_label">
							<label class="woo_dowd_product_ids_label" for="woo_dowd_product_ids">' . __( 'Product ids to include or exclude:', 'woo-dowd-delivery-day-discount' ) . '</label>
						</td>
						<td class="woo_dowd_product_ids_cell">
							<input type="text" name="woo_dowd_product_ids" id="woo_dowd_product_ids" value="' . get_option( 'woo_dowd_product_ids' ) . '"/>
						</td>
						<td class="woo_dowd_product_ids_radio">
							<input type="radio" name="woo_dowd_product_ids_in_ex" value="include" ' . $ids_include . '/>' . __( 'Include', 'woo-dowd-delivery-day-discount' ) .
						'</td>
						<td class="woo_dowd_product_ids_radio">
							<input type="radio" name="woo_dowd_product_ids_in_ex" value="exclude" ' . $ids_exclude . '/>' . __( 'Exclude', 'woo-dowd-delivery-day-discount' ) .
						'</td>
					</tr>
					<tr>
						<td class="woo_dowd_product_categories_label">
						<label class="woo_dowd_product_categories_label" for="woo_dowd_product_categories">' . __( 'Product categories to include or exclude:', 'woo-dowd-delivery-day-discount' ) . '</label>
						</td>
						<td class="woo_dowd_product_categories_cell">
							<input type="text" name="woo_dowd_product_categories" id="woo_dowd_product_categories" value="' . get_option( 'woo_dowd_product_categories' ) . '"/>
						</td>
						<td class="woo_dowd_product_categories_radio">
							<input type="radio" name="woo_dowd_product_categories_in_ex" value="include" ' . $categories_include . '/>' . __( 'Include', 'woo-dowd-delivery-day-discount' ) .
						'</td>
						<td class="woo_dowd_product_categories_radio">
							<input type="radio" name="woo_dowd_product_categories_in_ex" value="exclude" ' . $categories_exclude . '/>' . __( 'Exclude', 'woo-dowd-delivery-day-discount' ) .
						'</td>
					</tr>';
		$html .= '</tbody>
			</table>
		</fieldset>';

		echo $html;
	}

	/**
	 * Callback for adding the Day of Week discount table.
	 *
	 * Edits for the entered data:
	 * date-start      - either '' or a valid date in yyyy-mm-dd format.
	 * date-end        - either '' or a valid date in yyyy-mm-dd format.
	 * discount-amount - either ''/'0' for NO DISCOUNT or a number between 0 and 100 for a 'percent' discount-type.
	 * discount-type   - either 'percent' or 'amount'.
	 *
	 * @param array $args Callback arguments.
	 * @since 1.0
	 */
	public static function woo_dowd_dow_discount_table_callback( $args ) {
		global $woo_dowd_weekdays;
		$html  = '';
		$html .= '<fieldset class="woo-dowd-dow-discount-fieldset">';
		$html .= '    <table class="woo_dowd_dow_discount_table">';
		$html .= '       <thead>';
		$html .= '         <tr>';
		$html .= '           <th>' . __( 'Day of Week', 'woo-dowd-delivery-day-discount' ) . '</th>';
		$html .= '           <th>' . __( 'Start Date', 'woo-dowd-delivery-day-discount' ) . '</th>';
		$html .= '           <th>' . __( 'End Date', 'woo-dowd-delivery-day-discount' ) . '</th>';
		$html .= '           <th>' . __( 'Discount Type', 'woo-dowd-delivery-day-discount' ) . '</th>';
		$html .= '           <th>' . __( 'Discount Amount', 'woo-dowd-delivery-day-discount' ) . '</th>';
		$html .= '         </tr>';
		$html .= '       </thead>';
		$html .= '     <tbody>';
		foreach ( $woo_dowd_weekdays as $n => $day_name ) {
			if ( 'amount' === get_option( $n . '_discount_type' ) ) {
				$amount_selected  = 'selected';
				$percent_selected = '';
			} else {
				$amount_selected  = '';
				$percent_selected = 'selected';
			}
			$html .= '<tr>
			<td class="woo_dowd_dow_discount_cell">
				<label class="woo_dowd_dow_discount_cell_label" for="' . $day_name . '">' . $day_name . '</label>
			</td>
			<td class="woo_dowd_dow_discount_cell">
				<input type="date" name="' . $n . '_date_start" id="' . $n . '_date_start" value="' . get_option( $n . '_date_start' ) . '"/>
			</td>
			<td class="woo_dowd_dow_discount_cell">
				<input type="date" name="' . $n . '_date_end" id="' . $n . '_date_end" value="' . get_option( $n . '_date_end' ) . '"/>
			</td>
			<td class="woo_dowd_dow_discount_cell">
				<select name="' . $n . '_discount_type" id="' . $n . '_discount_type">
					<option value="percent" ' . $percent_selected . '>' . __( 'percent', 'woo-dowd-delivery-day-discount' ) . '</option>
					<option value="amount" ' . $amount_selected . '>' . __( 'amount', 'woo-dowd-delivery-day-discount' ) . '</option>
				</select>
			</td>
			<td class="woo_dowd_dow_discount_cell">
				<input type="input" name="' . $n . '_discount_amount" id="' . $n . '_discount_amount" value="' . get_option( $n . '_discount_amount' ) . '"/>
			</td>';
		}
		$html .= '</tbody>
			</table>
		</fieldset>';

		echo $html;
	}
}

$woo_dowd_settings = new Woo_Dowd_Settings();
