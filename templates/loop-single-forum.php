<?php

/**
 * Forums Loop - Single Forum
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;
// error_log('loop-single-forum.php');
?>
<ul id="bbp-forum-<?php bbp_forum_id(); ?>" <?php bbp_forum_class(); ?>>
	<li class="bbp-forum-info !float-none !w-auto">

		<?php if (bbp_is_user_home() && bbp_is_subscriptions()) : ?>

			<span class="bbp-row-actions">

				<?php do_action('bbp_theme_before_forum_subscription_action'); ?>

				<?php bbp_forum_subscription_link(array('before' => '', 'subscribe' => '+', 'unsubscribe' => '&times;')); ?>

				<?php do_action('bbp_theme_after_forum_subscription_action'); ?>

			</span>

		<?php endif; ?>

		<?php do_action('bbp_theme_before_forum_title'); ?>

		<a class="bbp-forum-title block !border-0 !border-b-4 !border-solid !border-gray-300 !text-center !text-xl !font-bold !pb-2 mb-6 relative flex items-center justify-center" href="<?php bbp_forum_permalink(); ?>">
			<?php bbp_forum_title(); ?>
			<span class="bbp-forum-topic-count absolute !right-0 !text-xs !text-gray-500 flex items-center h-full"><?php bbp_forum_topic_count(); ?>件</span></a>

		<?php do_action('bbp_theme_after_forum_title'); ?>

		<?php do_action('bbp_theme_before_forum_description'); ?>

		<div class="bbp-forum-content !mb-6"><?php bbp_forum_content(); ?></div>

		<?php do_action('bbp_theme_after_forum_description'); ?>

		<?php do_action('bbp_theme_before_forum_sub_forums'); ?>

		<?php bbp_list_forums(); ?>

		<?php do_action('bbp_theme_after_forum_sub_forums'); ?>

		<?php bbp_forum_row_actions(); ?>

	</li>

	<!-- <li class="bbp-forum-topic-count !float-none !w-auto">トピック数 <?php // bbp_forum_topic_count(); 
																		?></li> -->

	<!-- <li class="bbp-forum-reply-count !float-none">返信数 <?php // bbp_show_lead_topic() ? bbp_forum_reply_count() : bbp_forum_post_count(); 
															?></li> -->


	<?php
	// このフォーラムに属するトピック一覧を表示
	if (bbp_has_topics(array('post_parent' => bbp_get_forum_id(), 'posts_per_page' => 5))) : ?>
		<div class="bbp-forum-topics">
			<div class="space-y-2 !mb-8">
				<?php while (bbp_topics()) : bbp_the_topic(); ?>
					<div class="flex items-center justify-between">
						<a href="<?php bbp_topic_permalink(); ?>" class="text-base font-medium">
							<?php bbp_topic_title(); ?>
						</a>
						<div class="text-xs text-gray-500 flex flex-col items-end">
							<span>返信: <?php bbp_topic_reply_count(); ?></span>
							<span class="ml-3"><?php echo date('m/d H:i', strtotime(get_post_field('post_modified', bbp_get_topic_id()))); ?></span>
						</div>
					</div>
				<?php endwhile; ?>
			</div>
			<div class="text-center">
				<a href="<?php bbp_forum_permalink(); ?>"
					class="inline-block bg-blue-500 hover:bg-blue-500/80 !text-white hover:!text-white font-bold tracking-widest px-8 py-2 rounded-full">トピック一覧を見る</a>
			</div>
		</div>
	<?php endif; ?>

	<li class="bbp-forum-freshness !float-none !w-auto">

		<?php do_action('bbp_theme_before_forum_freshness_link'); ?>

		<?php // bbp_forum_freshness_link(); 
		?>

		<?php do_action('bbp_theme_after_forum_freshness_link'); ?>

		<p class="bbp-topic-meta hidden">

			<?php do_action('bbp_theme_before_topic_author'); ?>

			<span class="bbp-topic-freshness-author"><?php bbp_author_link(array('post_id' => bbp_get_forum_last_active_id(), 'size' => 14)); ?></span>

			<?php do_action('bbp_theme_after_topic_author'); ?>

		</p>
	</li>
</ul><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->