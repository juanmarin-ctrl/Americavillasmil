<?php
/**
 * Plugin Name: AV Studio Maestro
 * Description: Instala el sitio completo de AV Studio Academy con un solo clic (fotos, páginas Elementor, menús, inicio, CSS). Incluye log de instalación.
 * Version: 2.5.0
 * Author: TEDUCAS / AV Studio Academy
 * Requires at least: 6.0
 * Requires PHP: 7.4
 * Text Domain: av-studio-importer
 */

if (!defined('ABSPATH')) {
	exit;
}

define('AVSI_VERSION', '2.5.0');
define('AVSI_PATH', plugin_dir_path(__FILE__));
define('AVSI_URL', plugin_dir_url(__FILE__));

require_once AVSI_PATH . 'includes/class-avsi-importer.php';

/**
 * Body classes (server-side, sin esperar al JS) para que el CSS de marca aplique desde el primer paint.
 */
add_filter('body_class', function ($classes) {
	$classes[] = 'av-studio-site';
	if (avsi_pro_chrome_active()) {
		$classes[] = 'av-pro-chrome';
	}
	return $classes;
});

/**
 * ¿Nuestro header/footer de Elementor Pro (Theme Builder) está activo?
 */
function avsi_pro_chrome_active() {
	if (!class_exists('\\ElementorPro\\Plugin')) {
		return false;
	}
	$ids = get_option('avsi_pro_chrome_ids');
	if (empty($ids['header']) || empty($ids['footer'])) {
		return false;
	}
	return get_post_status((int) $ids['header']) === 'publish' && get_post_status((int) $ids['footer']) === 'publish';
}

/**
 * Hello Elementor: cuando Pro ya pinta nuestro header/footer, el tema no debe pintar el suyo (barra beige).
 */
add_filter('hello_elementor_header_footer', function ($show) {
	return avsi_pro_chrome_active() ? false : $show;
});

add_action('wp_enqueue_scripts', function () {
	wp_enqueue_style(
		'avsi-fonts',
		'https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;1,400&family=Manrope:wght@400;500;600;700&display=swap',
		array(),
		null
	);
	wp_enqueue_style(
		'avsi-elementor-custom',
		AVSI_URL . 'assets/elementor-custom.css',
		array(),
		AVSI_VERSION
	);
	wp_enqueue_style(
		'avsi-frontend',
		AVSI_URL . 'assets/av-frontend.css',
		array('avsi-elementor-custom'),
		AVSI_VERSION
	);
	wp_enqueue_script(
		'avsi-frontend',
		AVSI_URL . 'assets/av-frontend.js',
		array(),
		AVSI_VERSION,
		true
	);

	$monogram = AVSI_URL . 'assets/images/brand/monograma-av-oro.png';
	$logo_id = (int) get_theme_mod('custom_logo');
	if ($logo_id) {
		$url = wp_get_attachment_image_url($logo_id, 'full');
		if ($url) {
			$monogram = $url;
		}
	}
	wp_localize_script(
		'avsi-frontend',
		'avsiFrontend',
		array(
			'monogramUrl' => esc_url_raw($monogram),
		)
	);
}, 30);

add_action('admin_menu', function () {
	add_menu_page(
		'AV Studio Maestro',
		'AV Studio',
		'manage_options',
		'av-studio-importer',
		'avsi_render_admin_page',
		'dashicons-art',
		3
	);
});

add_action('admin_post_avsi_run_import', function () {
	if (!current_user_can('manage_options')) {
		wp_die('Forbidden');
	}
	check_admin_referer('avsi_run_import');

	@set_time_limit(300);
	wp_raise_memory_limit('admin');

	$importer = new AVSI_Importer();
	$result = $importer->run_full_site();

	set_transient('avsi_last_result', $result, 2 * HOUR_IN_SECONDS);
	update_option('avsi_last_result', $result, false);
	wp_safe_redirect(admin_url('admin.php?page=av-studio-importer&done=1'));
	exit;
});

function avsi_render_admin_page() {
	if (!current_user_can('manage_options')) {
		return;
	}

	$result = get_transient('avsi_last_result');
	if (!is_array($result)) {
		$result = get_option('avsi_last_result');
	}
	$log = array();
	if (is_array($result) && !empty($result['log'])) {
		$log = $result['log'];
	} else {
		$saved = get_option('avsi_last_log');
		if (is_array($saved)) {
			$log = $saved;
		}
	}

	$elementor_ok = did_action('elementor/loaded') || class_exists('\\Elementor\\Plugin');
	$pro_ok = class_exists('\\ElementorPro\\Plugin');
	$upload = wp_upload_dir();
	$log_file = !empty($upload['basedir']) ? trailingslashit($upload['basedir']) . 'avsi-import-log.txt' : '';
	?>
	<div class="wrap">
		<h1>AV Studio Maestro <small style="font-size:13px;color:#666;">v<?php echo esc_html(AVSI_VERSION); ?></small></h1>
		<p style="font-size:15px;max-width:760px;">Un botón instala el sitio. Después verás el <strong>log completo</strong> de lo que hizo (fotos, plantillas, elementos por página).</p>

		<?php if (!$elementor_ok) : ?>
			<div class="notice notice-error"><p><strong>Activa Elementor primero</strong> y vuelve aquí.</p></div>
		<?php endif; ?>

		<?php if ($elementor_ok && !$pro_ok) : ?>
			<div class="notice notice-info"><p>Elementor Free: header/footer se embebe en cada página.</p></div>
		<?php endif; ?>

		<?php if (isset($_GET['done']) && is_array($result)) : ?>
			<div class="notice notice-success" style="padding:12px 16px;">
				<p style="font-size:16px;margin:0 0 8px;"><strong>Proceso terminado.</strong> Revisa el log abajo: si alguna página dice <code>FALLBACK-generic</code> o <code>verify_els=0</code>, cópialo y mándalo.</p>
				<ul style="margin:0;">
					<li>Imágenes nuevas: <?php echo esc_html((string) ($result['images'] ?? 0)); ?></li>
					<li>Plantillas: <?php echo esc_html((string) ($result['templates'] ?? 0)); ?></li>
					<li>Páginas: <?php echo esc_html((string) ($result['pages'] ?? 0)); ?></li>
					<li>Posts: <?php echo esc_html((string) ($result['posts'] ?? 0)); ?></li>
				</ul>
				<p style="margin-top:12px;">
					<a class="button button-primary" href="<?php echo esc_url(home_url('/')); ?>" target="_blank">Ver sitio</a>
					<a class="button" href="<?php echo esc_url(admin_url('edit.php?post_type=page')); ?>">Páginas</a>
					<a class="button" href="<?php echo esc_url(admin_url('post.php?post=' . (int) get_option('page_on_front') . '&action=elementor')); ?>">Editar Inicio con Elementor</a>
				</p>
			</div>
		<?php endif; ?>

		<div class="card" style="max-width:760px;padding:1.5rem;border:1px solid #c4a35a;box-shadow:0 10px 30px rgba(0,0,0,.06);">
			<h2 style="margin-top:0;">Instalar / reparar</h2>
			<p>Sube el ZIP <strong>2.4.0</strong>, actívalo y pulsa el botón. El log dirá exactamente qué se guardó en cada página (plantillas completas, header/footer Pro activados, tarjetas en 3 columnas).</p>
			<form method="post" action="<?php echo esc_url(admin_url('admin-post.php')); ?>" onsubmit="this.querySelector('input[type=submit]').disabled=true;this.querySelector('input[type=submit]').value='Instalando… espera';">
				<input type="hidden" name="action" value="avsi_run_import">
				<?php wp_nonce_field('avsi_run_import'); ?>
				<?php
				submit_button(
					'REPARAR / INSTALAR SITIO COMPLETO',
					'primary large',
					'submit',
					false,
					$elementor_ok ? array('style' => 'background:#c4a35a;border-color:#9a7a3c;font-size:16px;padding:8px 22px;') : array('disabled' => 'disabled')
				);
				?>
			</form>
		</div>

		<?php if (!empty($log)) : ?>
			<div class="card" style="max-width:960px;margin-top:20px;padding:1.25rem;">
				<h2 style="margin-top:0;">Log de instalación</h2>
				<p style="margin-top:0;color:#666;">También se guarda en: <code><?php echo esc_html($log_file); ?></code></p>
				<textarea readonly rows="22" style="width:100%;font-family:Consolas,Monaco,monospace;font-size:12px;background:#111;color:#d6ffd6;padding:12px;border-radius:8px;"><?php echo esc_textarea(implode("\n", $log)); ?></textarea>
			</div>
		<?php else : ?>
			<div class="card" style="max-width:760px;margin-top:20px;padding:1.25rem;">
				<h2 style="margin-top:0;">Log</h2>
				<p>Aún no hay log. Ejecuta la instalación y aparecerá aquí línea por línea.</p>
			</div>
		<?php endif; ?>
	</div>
	<?php
}
