<?php

/**
 * Forums Loop
 *
 * @package bbPress
 * @subpackage Theme
 */

// Exit if accessed directly
defined('ABSPATH') || exit;
do_action('bbp_template_before_forums_loop');

?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer" />

<style>
@keyframes like-grow { from { width: 0 } to { width: var(--w) } }
.like-bar { animation: like-grow 1.4s cubic-bezier(.22,1,.36,1) forwards }

@keyframes medal-in { from { opacity:0; transform: translateY(-2px) } to { opacity:1; transform:none } }
.medal-badge { opacity:0; animation: medal-in .35s ease-out 1.4s forwards }
</style>

<?php
global $wpdb;

$like_rows = $wpdb->get_results($wpdb->prepare(
    "SELECT p.post_author AS user_id,
            SUM(CAST(pm.meta_value AS UNSIGNED)) AS total_likes
     FROM {$wpdb->posts} p
     JOIN {$wpdb->postmeta} pm
       ON pm.post_id = p.ID AND pm.meta_key = %s
     WHERE p.post_type = %s
       AND p.post_status = %s
     GROUP BY p.post_author
     ORDER BY p.post_author ASC
     LIMIT 10",
     'reply_likes', 'reply', 'publish'
));

if ( ! empty( $like_rows ) ) :

  $max_likes = 0;
  foreach ($like_rows as $r) { $max_likes = max($max_likes, (int)$r->total_likes); }

  $sorted = $like_rows;
  usort($sorted, function($a, $b){
    return (int)$b->total_likes <=> (int)$a->total_likes;
  });
  $top3 = array_slice($sorted, 0, 3);
  $rank_map = []; // user_id => rank(1..3)
  $rank_num = 1;
  foreach ($top3 as $row) {
    $uid_top = (int)$row->user_id;
    if (!isset($rank_map[$uid_top])) {
      $rank_map[$uid_top] = $rank_num++;
      if ($rank_num > 3) break;
    }
  }
?>
<div class="clear-both h-4"></div>
  <section class="bg-blue-50/50 rounded-lg border border-blue-200 p-4">
    <div class="mb-3 text-center">
      <h2 class="text-xl font-bold tracking-tight !mt-0">みんなの「いいね」ランキング</h2>
    </div>

    <ul class="divide-y divide-slate-200">
    <?php foreach ( $like_rows as $row ) :
        $uid   = (int) $row->user_id;
        $likes = (int) $row->total_likes;
        $user  = get_userdata($uid);
        $name  = $user ? $user->display_name : '（退会ユーザー）';
        $profile_url = $user ? get_author_posts_url($uid) : '#';
        // 見やすさのため最小8%（0件でも少し伸びる）
        $ratio = $max_likes > 0 ? max(0.08, min(1, $likes / $max_likes)) : 0.08;

        // 上位3なら rank を取得
        $rank = isset($rank_map[$uid]) ? (int)$rank_map[$uid] : null;
        $rank_color = $rank === 1 ? 'text-amber-500' : ($rank === 2 ? 'text-slate-400' : 'text-orange-500');
    ?>
        <li class="relative mb-2">
        <div class="relative flex items-center gap-3">
            <div class="relative shrink-0 w-[40px]">
            <?php echo get_avatar( $uid, 40, '', '', ['class' => 'rounded-full w-full h-full object-cover'] ); ?>
            </div>

            <!-- 伸びるバー -->
            <div class="min-w-0 flex-1">
            <div class="flex items-center gap-2">
                <a href="<?php echo esc_url($profile_url); ?>" class="font-medium hover:underline line-clamp-1">
                <?php echo esc_html($name); ?>
                </a>
                <?php if ($rank): ?>
                <span class="medal-badge inline-flex items-center gap-1 rounded-full bg-white/85 ring-1 ring-slate-200 px-2 py-0.5 text-xs font-semibold shadow-sm">
                    <i class="fa-solid fa-medal text-sm <?php echo esc_attr($rank_color); ?>"></i>
                    <span><?php echo (string)$rank; ?>位</span>
                </span>
                <?php endif; ?>
            </div>
            <div class="mt-1 h-2 w-full rounded-full bg-slate-100">
                <div
                class="h-2 rounded-full like-bar bg-blue-600"
                style="--w: <?php echo esc_attr($ratio * 100); ?>%; width: var(--w);">
                </div>
            </div>
            </div>
            <div class="shrink-0">
            <span class="inline-flex items-center rounded-full border border-slate-200 px-2.5 py-0.5 text-xs font-medium">
                <?php echo esc_html(number_format($likes)); ?> いいね
            </span>
            </div>
        </div>
        </li>
    <?php endforeach; ?>
    </ul>
  </section>
<?php endif; ?>

<ul id="forums-list-<?php bbp_forum_id(); ?>"
    class="bbp-forums !border-none">
    <li class="bbp-header hidden">

        <ul class="forum-titles">
            <li class="bbp-forum-info"><?php esc_html_e('Forum', 'bbpress'); ?></li>
            <li class="bbp-forum-topic-count"><?php esc_html_e('Topics', 'bbpress'); ?></li>
            <li class="bbp-forum-reply-count"><?php bbp_show_lead_topic()
                                                    ? esc_html_e('Replies', 'bbpress')
                                                    : esc_html_e('Posts',   'bbpress');
                                                ?></li>
            <li class="bbp-forum-freshness"><?php esc_html_e('Last Post', 'bbpress'); ?></li>
        </ul>

    </li><!-- .bbp-header -->

    <li class="bbp-body flex flex-wrap gap-8 justify-between py-8">

        <?php while (bbp_forums()) : bbp_the_forum(); ?>

            <?php bbp_get_template_part('loop', 'single-forum'); ?>

        <?php endwhile; ?>

    </li><!-- .bbp-body -->

    <li class="bbp-footer hidden">

        <div class="tr">
            <p class="td colspan4">&nbsp;</p>
        </div><!-- .tr -->

    </li><!-- .bbp-footer -->
</ul><!-- .forums-directory -->

<?php do_action('bbp_template_after_forums_loop'); ?>

<?php
// 解除POST処理（全員表示のまま）※必要なら権限チェックを加えてください
if ( isset($_POST['bbp_unsub_user'], $_POST['bbp_unsub_post']) && check_admin_referer('bbp_unsub_front') ) {
  $uid = (int) $_POST['bbp_unsub_user'];
  $pid = (int) $_POST['bbp_unsub_post'];
  delete_post_meta( $pid, '_bbp_subscription', $uid );
}

global $wpdb;

// ★最小変更：p.ID（post_id）も文字列に含める
$subs = $wpdb->get_results("
  SELECT m.meta_value AS user_id,
         GROUP_CONCAT(CONCAT(p.post_type, ':', p.ID, ' - ', p.post_title) SEPARATOR ' | ') AS subscriptions
  FROM {$wpdb->postmeta} AS m
  JOIN {$wpdb->posts}   AS p ON p.ID = m.post_id
  WHERE m.meta_key = '_bbp_subscription'
    AND p.post_type IN ('forum', 'topic')
  GROUP BY m.meta_value
  ORDER BY m.meta_value ASC
");
?>
<section class="my-6 p-4 bg-slate-50 rounded-md border border-slate-200 text-sm overflow-x-auto">
  <h3 class="font-bold mb-2">ユーザーごとの通知受付一覧</h3>
  <table class="min-w-full border border-slate-300 text-sm">
    <thead class="bg-slate-100">
      <tr>
        <th class="px-2 py-1 text-left border-b border-slate-300 w-48">ユーザー名</th>
        <th class="px-2 py-1 text-left border-b border-slate-300">購読一覧</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ( $subs as $row ) :
        $user = get_userdata( $row->user_id );
        $name = $user ? $user->display_name : '（不明なユーザーID: '. intval($row->user_id) .'）';
      ?>
        <tr>
          <td class="px-2 py-1 align-top font-medium border-b border-slate-200">
            <?php echo esc_html( $name ); ?>
          </td>
          <td class="px-2 py-1 border-b border-slate-200 text-slate-700">
            <?php
              // "forum:255 - タイトル | topic:300 - タイトル..." を分割
              $items = explode(' | ', $row->subscriptions);
              echo '<ul class="flex flex-col gap-1">';
              foreach ( $items as $item ) {
                // 先頭の "forum:255" / "topic:300" と "タイトル" に分割
                $parts = explode(' - ', $item, 2);
                $typeId = $parts[0] ?? '';
                $title  = $parts[1] ?? '';

                // "forum:255" -> [$type='forum', $post_id=255]
                $type = '';
                $post_id = 0;
                if (strpos($typeId, ':') !== false) {
                  list($type, $post_id) = explode(':', $typeId, 2);
                }
                $post_id = (int) $post_id;

                // 表示ラベル置換（forum→グループ / topic→トピック）
                $label = ($type === 'forum') ? 'グループ' : (($type === 'topic') ? 'トピック' : $type);

                echo '<li class="flex items-center justify-between text-xs">';
                echo '<span>';
                echo esc_html( $label . ' - ' . $title );
                echo '</span> ';

                // 通知解除ボタン（post_id と user_id を送る）
                echo '<form method="post" onsubmit="return confirm(\'この購読を解除しますか？\');">';
                wp_nonce_field('bbp_unsub_front');
                echo '<input type="hidden" name="bbp_unsub_user" value="' . esc_attr($row->user_id) . '">';
                echo '<input type="hidden" name="bbp_unsub_post" value="' . esc_attr($post_id) . '">';
                echo '<button type="submit" class="bg-red-100 border-none text-red-600 py-1 hover:underline">メール通知解除</button>';
                echo '</form>';

                echo '</li>';
              }
              echo '</ul>';
            ?>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</section>