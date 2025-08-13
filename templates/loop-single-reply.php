<?php

/**
 * Replies Loop - Single Reply
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

?>
<div class="rounded-md shadow-sm overflow-hidden border-solid border-2 border-gray-200 mb-4">
	<div id="post-<?php bbp_reply_id(); ?>" class="bbp-reply-header !border-none">
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

	<div <?php bbp_reply_class(); ?>>
		<div class="bbp-reply-author">

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

		<div class="bbp-reply-content">

			<?php do_action('bbp_theme_before_reply_content'); ?>

			<?php bbp_reply_content(); ?>

			<?php do_action('bbp_theme_after_reply_content'); ?>

			<?php
			$reply_id = bbp_get_reply_id();
			$like_url = get_like_url($reply_id);
			$current_likes = (int) get_post_meta($reply_id, 'reply_likes', true);
			?>
			
			<!-- 最初のトピックのにはいいね機能を表示しない -->
			<?php if (bbp_get_reply_topic_id() === bbp_get_topic_id(0)) : ?>
			<div class=" inline-block px-2 py-1 bg-gray-100 rounded-full border-solid border-2 border-gray-200">
				<a href="<?php echo esc_url($like_url); ?>" class="!flex items-center gap-1 !text-pink-500 hover:!text-pink-600 !no-underline">
					<i class="fas fa-heart !text-pink-400 hover:text-pink-600"></i><span>いいね</span>
					<span class="font-bold"><?php echo $current_likes; ?></span>
				</a>
			</div>
			<?php endif; ?>
		</div><!-- .bbp-reply-content -->
	</div><!-- .reply -->
</div>