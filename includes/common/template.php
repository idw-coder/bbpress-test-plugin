<?php
if (!defined('ABSPATH')) exit;

/**
 * 購読トグルの <span id="subscription-toggle"> 直下に
 * 通知ユーザー名チップを常時挿入（フォーラム/トピック共通）
 */
add_filter('bbp_get_user_subscribe_link', function($html, $r, $user_id, $object_id) {
  global $wpdb;

  // _bbp_subscription から対象IDの購読ユーザー取得
  $user_ids = $wpdb->get_col($wpdb->prepare(
    "SELECT meta_value FROM {$wpdb->postmeta}
     WHERE post_id=%d AND meta_key='_bbp_subscription'",
    intval($object_id)
  ));
  if (empty($user_ids)) return $html;

  // display_name に変換（重複除去）
  $names = [];
  foreach ($user_ids as $uid) {
    $u = get_userdata((int)$uid);
    if ($u) $names[$u->display_name] = true;
  }
  $names = array_keys($names);
  if (empty($names)) return $html;

  // ユーザー名チップ
  $chips = '';
  foreach ($names as $n) {
    $chips .= '<span class="inline-block rounded-full border px-2 py-0.5 text-xs mr-1 mb-1">'.esc_html($n).'</span>';
  }

  // <span id="subscription-toggle"> の先頭に差し込む
  $box  = '<span class="ml-2"><span class="mt-2 max-h-28 overflow-y-auto p-2 border border-slate-200 rounded bg-slate-50">';
  $box .= $chips . '</span></span>';

  return str_replace('<span id="subscription-toggle">', '<span id="subscription-toggle">'.$box, $html);
}, 10, 4);


/**
 * 通知メールのBcc廃止
 */

add_action('init', function () {
    add_action('bbp_new_reply', 'my_notify_topic_subscribers_individual', 10, 5);
}, 20);

add_filter('wp_mail', function($args){
  if (!empty($args['headers'])) {
    $headers = is_array($args['headers']) ? $args['headers'] : explode("\n", $args['headers']);
    $headers = array_values(array_filter($headers, fn($h) => stripos(trim($h), 'bcc:') !== 0));
    $args['headers'] = $headers;
  }
  return $args;
}, 20);

/**
 * 返信通知を購読者へ“個別送信”する
 */
function my_notify_topic_subscribers_individual($reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = array(), $reply_author = 0)
{
    if ( ! function_exists('bbp_is_subscriptions_active') || ! bbp_is_subscriptions_active() ) return;

    // 公開状態チェック（簡略）
    if ( ! bbp_is_topic_public($topic_id) || ! bbp_is_reply_published($reply_id) ) return;

    // 購読者取得（投稿者本人は除外）
    $user_ids = (array) bbp_get_subscribers($topic_id);
    $key = array_search( (int) $reply_author, $user_ids, true );
    if ( $key !== false ) unset($user_ids[$key]);
    if ( empty($user_ids) ) return;

    // メールアドレス化
    $emails = (array) bbp_get_email_addresses_from_user_ids($user_ids);
    if ( empty($emails) ) return;

    // 件名・本文（bbPressと同等のフォーマットを簡易再現）
    $forum_title = wp_specialchars_decode( strip_tags( bbp_get_forum_title($forum_id) ), ENT_QUOTES );
    $topic_title = wp_specialchars_decode( strip_tags( bbp_get_topic_title($topic_id) ), ENT_QUOTES );
    $reply_name  = wp_specialchars_decode( strip_tags( bbp_get_reply_author_display_name($reply_id) ), ENT_QUOTES );
    $reply_body  = wp_specialchars_decode( strip_tags( bbp_get_reply_content($reply_id) ), ENT_QUOTES );
    $reply_url   = bbp_get_reply_url($reply_id);

    $subject = apply_filters('bbp_subscription_mail_title', '[' . $forum_title . '] ' . $topic_title, $reply_id, $topic_id);

    $message = sprintf(
        esc_html__('%1$s wrote:

%2$s

Post Link: %3$s

-----------

You are receiving this email because you subscribed to a forum topic.

Login and visit the topic to unsubscribe from these emails.', 'bbpress'),
        $reply_name,
        $reply_body,
        $reply_url
    );
    $message = apply_filters('bbp_subscription_mail_message', $message, $reply_id, $topic_id);

    if ( empty($subject) || empty($message) ) return;

    // From ヘッダー（noreply@）
    $headers   = array( bbp_get_email_header() );
    $no_reply  = bbp_get_do_not_reply_address();
    $from_mail = apply_filters('bbp_subscription_from_email', $no_reply);
    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from_mail . '>';

    // Bcc を使わず “1人ずつ”送信
    foreach ($emails as $to) {
        wp_mail($to, $subject, $message, $headers);
    }
}