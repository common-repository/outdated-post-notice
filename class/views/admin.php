<?php
/**
 * @package   FSOutdatedPostNotice
 * @author    Firdaus Zahari <fake@fsylum.net>
 * @license   GPL-2.0+
 * @link      http://fsylum.net
 * @copyright 2014 Firdaus Zahari
 */
?>

<div class="wrap">

	<?php screen_icon(); ?>
	<h2><?php echo esc_html( get_admin_page_title() ); ?></h2>
    <form action="options.php" method="post">
        <?php settings_fields( $this->settings_name ); ?>
        <?php do_settings_sections( $this->plugin_slug ); ?>
        <input type="submit" class="button-primary" value="Save Changes">
    </form>

</div>
