<?php
	/**
	 * ShopLentor Template Library - Elementor Editor Templates
	 */
	if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly
?>

<script type="text/template" id="tmpl-woolentor-template-library-header-actions">
	<div id="woolentor-template-library-header-sync" class="elementor-templates-modal__header__item">
		<i class="eicon-sync" aria-hidden="true" title="<?php esc_attr_e( 'Sync Library', 'woolentor' ); ?>"></i>
		<span class="elementor-screen-only"><?php echo esc_html__( 'Sync Library', 'woolentor' ); ?></span>
	</div>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-logo">
	<span class="tmpl-woolentor-template-library-logo-area">
		<img src="<?php echo esc_url( WOOLENTOR_ADDONS_PL_URL . 'includes/admin/assets/images/logo.png' ); ?>" />
	</span>
	<span class="tmpl-woolentor-template-library-logo-title">{{{ title }}}</span>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-header-menu">
	<# _.each( tabs, function( args, tab ) { var activeClass = args.active ? 'elementor-active' : ''; #>
	<div class="elementor-component-tab elementor-template-library-menu-item {{activeClass}}" data-tab="{{{ tab }}}">{{{ args.title }}}</div>
	<# } ); #>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-header-menu-responsive">
	<div class="elementor-component-tab woolentor-template-library-responsive-menu-item elementor-active" data-tab="desktop">
		<i class="eicon-device-desktop" aria-hidden="true" title="<?php esc_attr_e( 'Desktop view', 'woolentor' ); ?>"></i>
		<span class="elementor-screen-only"><?php esc_html_e( 'Desktop view', 'woolentor' ); ?></span>
	</div>
	<div class="elementor-component-tab woolentor-template-library-responsive-menu-item" data-tab="tab">
		<i class="eicon-device-tablet" aria-hidden="true" title="<?php esc_attr_e( 'Tab view', 'woolentor' ); ?>"></i>
		<span class="elementor-screen-only"><?php esc_html_e( 'Tab view', 'woolentor' ); ?></span>
	</div>
	<div class="elementor-component-tab woolentor-template-library-responsive-menu-item" data-tab="mobile">
		<i class="eicon-device-mobile" aria-hidden="true" title="<?php esc_attr_e( 'Mobile view', 'woolentor' ); ?>"></i>
		<span class="elementor-screen-only"><?php esc_html_e( 'Mobile view', 'woolentor' ); ?></span>
	</div>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-loading">
	<div class="elementor-loader-wrapper">
		<div class="elementor-loader">
			<div class="elementor-loader-boxes">
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
				<div class="elementor-loader-box"></div>
			</div>
		</div>
		<div class="elementor-loading-title"><?php echo esc_html__( 'Loading', 'woolentor' ); ?></div>
	</div>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-preview">
	<iframe></iframe>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-insert-button">
	<a class="elementor-template-library-template-action woolentor-template-library-template-insert elementor-button">
		<i class="eicon-file-download" aria-hidden="true"></i>
		<span class="elementor-button-title"><?php echo esc_html__( 'Insert', 'woolentor' ); ?></span>
	</a>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-get-pro-button">
	<a class="elementor-template-library-template-action elementor-button elementor-go-pro" href="https://woolentor.com/pricing/?utm_source=elementor-editor&utm_medium=template-library" target="_blank">
		<i class="eicon-external-link-square" aria-hidden="true"></i>
		<span class="elementor-button-title"><?php echo esc_html__( 'Go Pro', 'woolentor' ); ?></span>
	</a>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-header-insert">
	<div id="elementor-template-library-header-preview-insert-wrapper" class="elementor-templates-modal__header__item">
		{{{ woolentor.library.getModal().getTemplateActionButton( obj ) }}}
	</div>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-header-back">
	<i class="eicon-chevron-left" aria-hidden="true"></i>
	<span><?php echo esc_html__( 'Back to Library', 'woolentor' ); ?></span>
</script>

<script type="text/template" id="tmpl-woolentor-template-library-templates">
	<div id="elementor-template-library-toolbar">

		<div id="woolentor-template-library-filter-area-wrapper">
			<div id="elementor-template-library-filter-category-wrapper">
				<select id="elementor-template-library-filter-category">
					<option value=""><?php esc_html_e( 'All Categories', 'woolentor' ); ?></option>
					<# _.each( woolentor.library.getCategories(), function( category ) { #>
						<option value="{{{ category }}}">{{{ category }}}</option>
					<# } ); #>
				</select>
			</div>
		</div>

		<div id="elementor-template-library-filter-text-wrapper">
			<label for="woolentor-template-library-filter-text" class="elementor-screen-only"><?php esc_html_e( 'Search Templates:', 'woolentor' ); ?></label>
			<input id="woolentor-template-library-filter-text" placeholder="<?php esc_attr_e( 'Search', 'woolentor' ); ?>">
			<i class="eicon-search"></i>
		</div>

	</div>

	<div class="woolentor-template-library-window">
		<div id="woolentor-template-library-list"></div>
	</div>

</script>

<script type="text/template" id="woolentor-template-library-template">

	<div class="elementor-template-library-template-body">
		<# if ( 'page' === type ) { #>
			<div class="elementor-template-library-template-screenshot" style="background-image: url({{ thumbnail }});"></div>
		<# } else { #>
			<img class="woolentor-template-library-thumbnail" src="{{ thumbnail }}">
		<# } #>
		<div class="woolentor-template-library-preview">
			<i class="eicon-zoom-in-bold" aria-hidden="true"></i>
		</div>
		<# if ( obj.isPro ) { #>
		<span class="woolentor-template-library-pro-badge"><?php esc_html_e( 'Pro', 'woolentor' ); ?></span>
		<# } #>
	</div>

	<div class="woolentor-template-library-footer">
		<div class="woolentor-template-library-template-info">
			<h5 class="woolentor-template-library-template-title">{{{ title }}}</h5>
			<h6 class="woolentor-template-library-template-type">{{{ type }}}</h6>
		</div>

		<div class="woolentor-template-library-template-action-btn">
			{{{ woolentor.library.getModal().getTemplateActionButton( obj ) }}}
			<a href="#" class="elementor-button woolentor-template-library-preview-button">
				<i class="eicon-device-desktop" aria-hidden="true"></i>
				<?php esc_html_e( 'Preview', 'woolentor' ); ?>
			</a>
		</div>

	</div>

</script>

<script type="text/template" id="tmpl-elementor-woolentor-library-templates-empty">
	<div class="elementor-template-library-blank-icon">
		<img src="<?php echo esc_url( ELEMENTOR_ASSETS_URL . 'images/no-search-results.svg' ); ?>" class="elementor-template-library-no-results" />
	</div>
	<div class="elementor-template-library-blank-title"></div>
	<div class="elementor-template-library-blank-message"></div>
	<div class="elementor-template-library-blank-footer">
		<?php echo esc_html__( 'Want to learn more about the ShopLentor library?', 'woolentor' ); ?>
		<a class="elementor-template-library-blank-footer-link" href="<?php echo esc_url( 'https://woolentor.com' ); ?>" target="_blank"><?php echo esc_html__( 'Click here', 'woolentor' ); ?></a>
	</div>
</script>
