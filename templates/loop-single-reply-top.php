<?php

/**
 * Replies Loop - Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

/**
 * ユーザーIDに基づいて一貫した背景色を生成する関数
 */
if (!function_exists('get_user_background_color')) {
	function get_user_background_color($user_id)
	{
		// ユーザーIDをハッシュして一貫した数値を生成
		$hash = crc32($user_id);

		// 色の配列（青系統をベースにした落ち着いた色合い）
		$colors = [
			['bg' => '!bg-blue-50', 'border' => '!border-blue-200'],
			['bg' => '!bg-stone-50', 'border' => '!border-stone-200'],
			['bg' => '!bg-gray-50', 'border' => '!border-gray-200'],
			['bg' => '!bg-indigo-50', 'border' => '!border-indigo-200'],
			['bg' => '!bg-blue-100', 'border' => '!border-blue-300'],
			['bg' => '!bg-slate-100', 'border' => '!border-slate-300'],
			['bg' => '!bg-gray-100', 'border' => '!border-gray-300'],
		];

		// ハッシュ値を使って色を選択（同じユーザーIDなら常に同じ色）
		$color_index = abs($hash) % count($colors);

		return $colors[$color_index];
	}
}

// 現在のリプライのユーザーIDを取得
$reply_author_id = bbp_get_reply_author_id();
$user_colors = get_user_background_color($reply_author_id);
$user_bg_color = $user_colors['bg'];
$user_border_color = $user_colors['border'];

?>
<div class="rounded-lg shadow-sm overflow-hidden border border-gray-200 mb-4 <?php echo esc_attr($user_bg_color); ?> <?php echo esc_attr($user_border_color); ?>">
	<div id="post-<?php bbp_reply_id(); ?>" class="bbp-reply-header !border-none !bg-white">
		<div class="bbp-meta">
			<span class="bbp-reply-post-date"><?php bbp_reply_post_date(); ?></span>

			<?php if (bbp_is_single_user_replies()) : ?>

				<span class="bbp-header">
					<?php esc_html_e('in reply to: ', 'bbpress'); ?>
					<a class="bbp-topic-permalink" href="<?php bbp_topic_permalink(bbp_get_reply_topic_id()); ?>"><?php bbp_topic_title(bbp_get_reply_topic_id()); ?></a>
				</span>

			<?php endif; ?>

			<a href="<?php bbp_reply_url(); ?>" class="bbp-reply-permalink">#<?php bbp_reply_id(); ?></a>

			<?php do_action('bbp_theme_before_reply_admin_links'); ?>

			<?php bbp_reply_admin_links(); ?>

			<?php do_action('bbp_theme_after_reply_admin_links'); ?>

		</div><!-- .bbp-meta -->
	</div><!-- #post-<?php bbp_reply_id(); ?> -->

	<div class="px-4 py-2">
		<p class="text-lg font-medium text-gray-700">
			<?php echo get_the_title(get_post_meta(bbp_get_reply_id(), '_bbp_topic_id', true)); ?>
		</p>
	</div>

	<div <?php bbp_reply_class(); ?> style="background-color: transparent!important; display: flex; gap: 1rem; padding: 1rem;">
		<div class="bbp-reply-content flex-1 !float-none">

			<?php do_action('bbp_theme_before_reply_content'); ?>

			<?php bbp_reply_content(); ?>

			<?php do_action('bbp_theme_after_reply_content'); ?>

			<!-- 最初のトピックのにはいいね機能を表示しない -->

		</div><!-- .bbp-reply-content -->

		<div class="bbp-reply-author !float-none">

			<?php do_action('bbp_theme_before_reply_author_details'); ?>

			<?php bbp_reply_author_link(array('show_role' => false)); ?>

			<?php if (current_user_can('moderate', bbp_get_reply_id())) : ?>

				<?php do_action('bbp_theme_before_reply_author_admin_details'); ?>

				<!-- <div class="bbp-reply-ip"><?php // bbp_author_ip( bbp_get_reply_id() ); 
												?></div> -->

				<?php do_action('bbp_theme_after_reply_author_admin_details'); ?>

			<?php endif; ?>

			<?php do_action('bbp_theme_after_reply_author_details'); ?>

		</div><!-- .bbp-reply-author -->
	</div><!-- .reply -->
</div>