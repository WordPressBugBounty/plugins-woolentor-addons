<?php
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

	$idsString = is_array( $products_ids ) ? implode( ',', $products_ids ) : '';
	$qtyString = ( isset( $products_qty ) && is_array( $products_qty ) ) ? implode( ',', $products_qty ) : '';

	// remove_query_arg() first, so a page that already carries wishsuitepids (a shared list the
	// visitor is looking at) does not end up with the parameter twice.
	$share_base = remove_query_arg( array( 'wishsuitepids', 'wishsuiteqty' ), get_the_permalink() );
	$share_args = array( 'wishsuitepids' => $idsString );
	if ( ! empty( $qtyString ) ) {
		$share_args['wishsuiteqty'] = $qtyString;
	}
	$share_link  = add_query_arg( $share_args, $share_base );
	// add_query_arg() percent encodes the value separators, turning a readable id list into
	// wishsuitepids=12%2C15. A comma is a legal sub-delimiter in a query string, so restore it
	// and keep the shared link human readable.
	$share_link  = str_replace( '%2C', ',', $share_link );
	$share_title = get_the_title();

	// Every network expects the URL percent encoded. Passing it raw truncated the shared link
	// at its first & on Facebook, Twitter and LinkedIn.
	$encoded_share_link  = rawurlencode( $share_link );
	$encoded_share_title = rawurlencode( $share_title );

	$thumb_id  = get_post_thumbnail_id();
	$thumb_url = wp_get_attachment_image_src( $thumb_id, 'thumbnail-size', true );
	$thumb_src = ! empty( $thumb_url[0] ) ? rawurlencode( $thumb_url[0] ) : '';

	$social_button_list = [
		'facebook' => [
			'title' => esc_html__( 'Facebook', 'woolentor' ),
			'url' 	=> 'https://www.facebook.com/sharer/sharer.php?u=' . $encoded_share_link,
		],
		'twitter' => [
			'title' => esc_html__( 'Twitter', 'woolentor' ),
			'url' 	=> 'https://twitter.com/share?url=' . $encoded_share_link . '&amp;text=' . $encoded_share_title,
		],
		'pinterest' => [
			'title' => esc_html__( 'Pinterest', 'woolentor' ),
			'url' 	=> 'https://pinterest.com/pin/create/button/?url=' . $encoded_share_link . '&amp;media=' . $thumb_src,
		],
		'linkedin' => [
			'title' => esc_html__( 'Linkedin', 'woolentor' ),
			'url' 	=> 'https://www.linkedin.com/shareArticle?mini=true&amp;url=' . $encoded_share_link . '&amp;title=' . $encoded_share_title,
		],
		'email' => [
			'title' => esc_html__( 'Email', 'woolentor' ),
			// The old value folded the subject, the body label and the URL into one string, so
			// the mail client received a subject of "Whislist&body=My whislist:<url>".
			'url' 	=> 'mailto:?subject=' . rawurlencode( esc_html__( 'Wishlist', 'woolentor' ) ) . '&amp;body=' . $encoded_share_link,
		],
		'reddit' => [
			'title' => esc_html__( 'Reddit', 'woolentor' ),
			'url' 	=> 'https://reddit.com/submit?url=' . $encoded_share_link . '&amp;title=' . $encoded_share_title,
		],
		'telegram' => [
			'title' => esc_html__( 'Telegram', 'woolentor' ),
			'url' 	=> 'https://telegram.me/share/url?url=' . $encoded_share_link,
		],
		'odnoklassniki' => [
			'title' => esc_html__( 'Odnoklassniki', 'woolentor' ),
			'url' 	=> 'https://connect.ok.ru/offer?url=' . $encoded_share_link . '&amp;title=' . $encoded_share_title,
		],
		'whatsapp' => [
			'title' => esc_html__( 'WhatsApp', 'woolentor' ),
			'url' 	=> 'https://wa.me/?text=' . $encoded_share_link,
		],
		'vk' => [
			'title' => esc_html__( 'VK', 'woolentor' ),
			'url' 	=> 'https://vk.com/share.php?url=' . $encoded_share_link,
		],
	];

	$default_buttons = [
        'facebook'   => esc_html__( 'Facebook', 'woolentor' ),
        'twitter'    => esc_html__( 'Twitter', 'woolentor' ),
        'pinterest'  => esc_html__( 'Pinterest', 'woolentor' ),
        'linkedin'   => esc_html__( 'Linkedin', 'woolentor' ),
        'telegram'   => esc_html__( 'Telegram', 'woolentor' ),
    ];
	$button_list      = woolentor_get_option( 'social_share_buttons','wishsuite_table_settings_tabs', $default_buttons );
	$button_text      = woolentor_get_option( 'social_share_button_title','wishsuite_table_settings_tabs', 'Share:' );
	$enable_copy_link = woolentor_get_option( 'enable_copy_link','wishsuite_table_settings_tabs', 'off' );
	$copy_label       = esc_html__( 'Copy link', 'woolentor' );
	$copied_label     = esc_html__( 'Copied!', 'woolentor' );

	if( is_array( $button_list ) ){

?>

<div class="wishsuite-social-share" data-share-base="<?php echo esc_url( $share_base ); ?>" data-title="<?php echo esc_attr( $share_title ); ?>" data-thumb="<?php echo esc_attr( $thumb_src ); ?>">
	<span class="wishsuite-social-title"><?php echo esc_html( $button_text ); ?></span>
	<ul>
		<?php
			foreach ( $button_list as $buttonkey => $button ) {
				if ( ! isset( $social_button_list[ $buttonkey ] ) ) {
					continue;
				}
				?>
				<li>
					<a rel="nofollow" data-platform="<?php echo esc_attr( $buttonkey ); ?>" href="<?php echo esc_url( $social_button_list[ $buttonkey ]['url'] ); ?>" aria-label="<?php echo esc_attr( $social_button_list[ $buttonkey ]['title'] ); ?>" <?php echo ( $buttonkey === 'email' ? '' : 'target="_blank"' ); ?>>
						<span class="wishsuite-social-icon">
							<?php echo wishsuite_icon_list( $buttonkey ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG from a fixed internal list. ?>
						</span>
					</a>
				</li>
				<?php
			}

			if ( 'on' === $enable_copy_link && ! empty( $idsString ) ) {
				?>
				<li>
					<button type="button" class="wishsuite-copy-link" data-clipboard="<?php echo esc_url( $share_link ); ?>" data-tooltip="<?php echo esc_attr( $copy_label ); ?>" data-copied="<?php echo esc_attr( $copied_label ); ?>" aria-label="<?php echo esc_attr( $copy_label ); ?>">
						<span class="wishsuite-social-icon">
							<?php echo wishsuite_icon_list( 'copy_link' ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped -- inline SVG from a fixed internal list. ?>
						</span>
					</button>
				</li>
				<?php
			}
		?>
	</ul>
</div>
<?php } ?>
