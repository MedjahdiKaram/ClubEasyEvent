<?php
/**
 * Settings page view.
 */
?>
<div class="wrap">
<h1><?php esc_html_e( 'Paramètres Club Easy Event', 'club-easy-event' ); ?></h1>
<form method="post" action="options.php">
<?php settings_fields( 'cee_settings_group' ); ?>
<?php do_settings_sections( 'cee_settings' ); ?>
<?php submit_button(); ?>
</form>
</div>
