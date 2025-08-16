<?php

/**
 * Replies Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;

do_action('bbp_template_before_replies_loop'); ?>

<ul id="topic-<?php bbp_topic_id(); ?>-replies" class="forums bbp-replies !border-none !overflow-visible">

	<li class="bbp-header hidden">
		<div class="bbp-reply-author"><?php esc_html_e('Author',  'bbpress'); ?></div><!-- .bbp-reply-author -->
		<div class="bbp-reply-content"><?php bbp_show_lead_topic()
											? esc_html_e('Replies', 'bbpress')
											: esc_html_e('Posts',   'bbpress');
										?></div><!-- .bbp-reply-content -->
	</li><!-- .bbp-header -->

	<li class="bbp-body">

		<?php if (bbp_thread_replies()) : ?>

			<?php bbp_list_replies(); ?>

		<?php else : ?>

			<?php while (bbp_replies()) : bbp_the_reply(); ?>

				<?php if (bbp_get_reply_topic_id() === bbp_get_topic_id(0)) : ?>
					<?php bbp_get_template_part('loop', 'single-reply'); ?>
				<?php else : ?>
					<?php bbp_get_template_part('loop', 'single-reply-top'); ?>
				<?php endif; ?>

			<?php endwhile; ?>

		<?php endif; ?>

	</li><!-- .bbp-body -->

	<li class="bbp-footer hidden">
		<div class="bbp-reply-author"><?php esc_html_e('Author',  'bbpress'); ?></div>
		<div class="bbp-reply-content"><?php bbp_show_lead_topic()
											? esc_html_e('Replies', 'bbpress')
											: esc_html_e('Posts',   'bbpress');
										?></div><!-- .bbp-reply-content -->
	</li><!-- .bbp-footer -->
</ul><!-- #topic-<?php bbp_topic_id(); ?>-replies -->

<?php do_action('bbp_template_after_replies_loop');
