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
<ul id="bbp-forum-<?php bbp_forum_id(); ?>" <?php bbp_forum_class(); ?> style="display: flex; flex-direction: column;">
	<?php // サムネイルを表示
	$thumbnail_url = get_forum_thumbnail_url(bbp_get_forum_id());
	$default_image = plugin_dir_url(dirname(__FILE__)) . 'assets/img/no_image.png';
	$image_url = $thumbnail_url ? $thumbnail_url : $default_image;
	?>
	<li class="!mb-6" style="margin: -2rem -2rem 1.5rem -2rem; width: calc(100% + 4rem); position: relative;">
		<img src="<?php echo $image_url; ?>" alt="<?php bbp_forum_title(); ?>" class="w-full h-auto" style="aspect-ratio: 18/9; object-fit: cover;">
		<div style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: linear-gradient(to  bottom, rgba(60, 60, 60, 0) 0%, rgba(60, 60, 60, 0.2) 100%); pointer-events: none;"></div>
	</li>
	<li class="bbp-forum-info !float-none !w-auto">

		<?php // if (bbp_is_user_home() && bbp_is_subscriptions()) : 
		?>

		<?php
		/**
		 * TODO: debug
		 * @var boolean
		 * 
		 * bbp_is_subscriptions() - 現在のページがユーザーの購読ページかどうかを判定
		 * 購読ページでのみ表示したい要素（購読解除ボタンなど）の表示制御に使用
		 */
		if (bbp_is_subscriptions()) : ?>

			<span class="bbp-row-actions">

				<?php do_action('bbp_theme_before_forum_subscription_action'); ?>

				<?php bbp_forum_subscription_link(array('before' => '', 'subscribe' => '+', 'unsubscribe' => '&times;')); ?>

				<?php do_action('bbp_theme_after_forum_subscription_action'); ?>

			</span>

		<?php endif; ?>

		<?php do_action('bbp_theme_before_forum_title'); ?>

		<a class="bbp-forum-title block !border-0 !border-b-2 !border-solid !border-gray-300 !text-center !text-xl !font-bold !pb-2 mb-6 relative flex items-center justify-center" href="<?php bbp_forum_permalink(); ?>">
			<?php bbp_forum_title(); ?>
			<span class="bbp-forum-topic-count absolute !right-0 !text-xs !text-gray-500 flex items-center h-full"><?php bbp_forum_topic_count(); ?>件</span>
		</a>

		<?php do_action('bbp_theme_after_forum_title'); ?>

		<?php do_action('bbp_theme_before_forum_description'); ?>

		<div class="bbp-forum-content !mb-6"><?php bbp_forum_content(); ?></div>

		<?php do_action('bbp_theme_after_forum_description'); ?>

		<?php do_action('bbp_theme_before_forum_sub_forums'); ?>

		<?php // bbp_list_forums(); 
		?>
		<?php
		// 現在のフォーラムの子フォーラムを取得（WP_Queryを使用）
		$current_forum_id = bbp_get_forum_id();
		if ($current_forum_id) {
			$sub_forums_query = new WP_Query(array(
				'post_type' => bbp_get_forum_post_type(),
				'post_parent' => $current_forum_id,
				'posts_per_page' => -1,
				'orderby' => 'menu_order',
				'order' => 'ASC'
			));

			if ($sub_forums_query->have_posts()) : ?>
				<div class="bbp-sub-forums mt-6">
					<div class="space-y-4 mb-6">
						<?php while ($sub_forums_query->have_posts()) : $sub_forums_query->the_post(); ?>
							<div class="bg-white rounded-sm p-4">
								<a href="<?php bbp_get_forum_permalink(get_the_ID()); ?>" class="block">
									<h4 class="font-medium !mt-0 mb-2"><?php the_title(); ?></h4>
									<?php if (get_the_content()) : ?>
										<p class="text-gray-600 mb-3"><?php echo wp_trim_words(get_the_content(), 20); ?></p>
									<?php endif; ?>
									<div class="text-gray-500 text-right">
										<span>トピック: <?php echo bbp_get_forum_topic_count(get_the_ID()) ? bbp_get_forum_topic_count(get_the_ID()) : 0; ?></span>
										<span class="ml-3">投稿: <?php echo bbp_get_forum_post_count(get_the_ID()) ? bbp_get_forum_post_count(get_the_ID()) : 0; ?></span>
									</div>
								</a>
							</div>
						<?php endwhile; ?>
					</div>
				</div>
	</li>
	<li>

		<div class="text-center">
			<a href="<?php bbp_forum_permalink(); ?>"
				class="inline-block bg-blue-500 hover:bg-blue-500/80 !text-white hover:!text-white font-bold tracking-widest px-8 py-2 rounded-full">フォーラム一覧を見る</a>
		</div>
	</li>
<?php endif;
			wp_reset_postdata(); // クエリをリセット
		}
?>

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
	</div>
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
	<li class="!mt-auto">
		<div class="text-center">
			<a href="<?php bbp_forum_permalink(); ?>"
				class="inline-block bg-blue-500 hover:bg-blue-500/80 !text-white hover:!text-white font-bold tracking-widest px-8 py-2 rounded-full">トピック一覧を見る</a>
		</div>
	</li>
<?php endif; ?>

</ul><!-- #bbp-forum-<?php bbp_forum_id(); ?> -->