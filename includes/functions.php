<?php

if ( ! defined( 'ABSPATH' ) )
	return; // Silence is Golden

function edd_db_register_metabox_input( $fields ) {
	$fields[] = '_edd_db_display_badge';

	return $fields;
}
add_filter( 'edd_metabox_fields_save', 'edd_db_register_metabox_input', 10, 1 );

function edd_db_display_metbox_input( $post_id ) {
	if ( empty( $post_id ) )
		return;

	$post_id = absint( $post_id );
	$current_setting = get_post_meta( $post_id, '_edd_db_display_badge', true );
	$output  = '<p><strong>' . __( 'Digital Badge', 'edd-db-txt' ) . '</strong></p>';
	$output .= '<p><input' . checked( '1', $current_setting, false ) . ' type="checkbox" id="_edd_db_display_badge" name="_edd_db_display_badge" value="1" />';
	$output .= '<label for="_edd_db_display_badge">' . __( 'Display an indication that the product is fulfilled via download', 'edd-db-txt' ) . '</label></p>';

	echo $output;
}
add_action( 'edd_meta_box_settings_fields', 'edd_db_display_metbox_input', 10, 1 );

function edd_db_badge_string() {
	$string = '<span class="edd-db-badge">' . edd_db_get_badge_string() . '</span>';

	return apply_filters( 'edd_db_badge_string', $string );
}

function edd_db_get_badge_string() {
	$badge_text = edd_get_option( 'EDD_Digital_Badge_badge_text', '[' . __( 'digital', 'edd-db-txt' ) . ']' );

	return apply_filters( 'edd_db_default_badge_string', $badge_text );
}

function edd_db_is_digital_download( $download_id = 0 ) {
	if ( empty( $download_id ) )
		return false;

	$display_badge = get_post_meta( $download_id, '_edd_db_display_badge', true );

	$is_digital = $display_badge === '1' ? true : false;

	return apply_filters( 'edd_db_is_digital_download', $is_digital, $download_id );
}

function edd_db_append_title( $title, $post_id ) {
	if ( 'download' !== get_post_type( $post_id ) || edd_is_checkout() )
		return $title;

	if ( ! edd_db_is_digital_download( $post_id ) )
		return $title;

	$digital_string = edd_db_get_badge_string();
	$badge = edd_db_badge_string();

	$string = $title . $badge;

	return $string;
}
add_filter( 'the_title', 'edd_db_append_title', 10, 2 );

function edd_db_add_badge_column_checkout( $item ) {
	if ( false === edd_db_is_digital_download( $item['id'] ) )
		return;

	echo '<td class="edd-db-checkout-cell">' . edd_db_badge_string() . '</td>';
}
add_action( 'edd_checkout_table_body_last', 'edd_db_add_badge_column_checkout', 10, 1 );