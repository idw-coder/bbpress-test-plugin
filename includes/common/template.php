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
// 購読ユーザーIDを空にしてデフォルトのBcc送信を抑止
add_filter('bbp_forum_subscription_user_ids', function($user_ids, $topic_id, $forum_id){ // forum購読
  return array();
}, 999, 3);

add_filter('bbp_topic_subscription_user_ids', function($user_ids, $reply_id, $topic_id){ // topic購読
  return array();
}, 999, 3);

/**
 * フォーラム購読者へ新規トピック通知（To送信）
 */
add_action('bbp_new_topic', 'my_notify_forum_via_to', 10, 4);
function my_notify_forum_via_to($topic_id = 0, $forum_id = 0, $anonymous_data = array(), $topic_author = 0)
{
    if (!function_exists('bbp_is_subscriptions_active') || !bbp_is_subscriptions_active()) return;
    if (!bbp_is_topic_public($topic_id)) return;

    if (!function_exists('bbp_get_forum_subscribers')) return;
    $user_ids = (array) bbp_get_forum_subscribers($forum_id);
    $user_ids = array_diff($user_ids, array((int)$topic_author));
    if (empty($user_ids)) return;

    if (!function_exists('bbp_get_email_addresses_from_user_ids')) return;
    $emails_raw = (array) bbp_get_email_addresses_from_user_ids($user_ids);
    $emails_map = [];
    foreach ($emails_raw as $e) {
        $e = sanitize_email($e);
        if ($e && is_email($e)) $emails_map[strtolower($e)] = $e;
    }
    $emails = array_values($emails_map);
    if (empty($emails)) return;

    $forum_title = wp_specialchars_decode(strip_tags(bbp_get_forum_title($forum_id)), ENT_QUOTES);
    $topic_title = wp_specialchars_decode(strip_tags(bbp_get_topic_title($topic_id)), ENT_QUOTES);
    $topic_url   = bbp_get_topic_permalink($topic_id);
    $author_name = wp_specialchars_decode(strip_tags(bbp_get_topic_author_display_name($topic_id)), ENT_QUOTES);

    $subject = sprintf('[%s] %s', $forum_title, $topic_title);
    $message = sprintf(
        "%s が新しいトピックを作成しました。\n\nトピック: %s\nリンク: %s\n\n—\nこのメールはフォーラムを購読しているため届いています。\n購読解除はログイン後、該当フォーラム/トピックのページから行えます。",
        $author_name,
        $topic_title,
        $topic_url
    );

    $headers   = [ bbp_get_email_header() ];
    $no_reply  = bbp_get_do_not_reply_address();
    $from_mail = apply_filters('bbp_subscription_from_email', $no_reply);
    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from_mail . '>';

    foreach (array_chunk($emails, 30) as $chunk) {
        wp_mail($chunk, $subject, $message, $headers);
    }
}

/**
 * トピック購読者＋フォーラム購読者へ返信通知（To送信）
 */
add_action('bbp_new_reply', 'my_notify_reply_via_to', 10, 5);
function my_notify_reply_via_to($reply_id = 0, $topic_id = 0, $forum_id = 0, $anonymous_data = array(), $reply_author = 0)
{
    if (!function_exists('bbp_is_subscriptions_active') || !bbp_is_subscriptions_active()) return;
    if (!bbp_is_topic_public($topic_id) || !bbp_is_reply_published($reply_id)) return;

    $topic_uids = (array) bbp_get_subscribers($topic_id);
    $forum_uids = function_exists('bbp_get_forum_subscribers') ? (array) bbp_get_forum_subscribers($forum_id) : array();

    $user_ids = array_unique(array_merge($topic_uids, $forum_uids));
    $user_ids = array_diff($user_ids, array((int)$reply_author));
    if (empty($user_ids)) return;

    if (!function_exists('bbp_get_email_addresses_from_user_ids')) return;
    $emails_raw = (array) bbp_get_email_addresses_from_user_ids($user_ids);
    $emails_map = [];
    foreach ($emails_raw as $e) {
        $e = sanitize_email($e);
        if ($e && is_email($e)) $emails_map[strtolower($e)] = $e;
    }
    $emails = array_values($emails_map);
    if (empty($emails)) return;

    $forum_title = wp_specialchars_decode(strip_tags(bbp_get_forum_title($forum_id)), ENT_QUOTES);
    $topic_title = wp_specialchars_decode(strip_tags(bbp_get_topic_title($topic_id)), ENT_QUOTES);
    $reply_name  = wp_specialchars_decode(strip_tags(bbp_get_reply_author_display_name($reply_id)), ENT_QUOTES);
    $reply_body  = wp_specialchars_decode(strip_tags(bbp_get_reply_content($reply_id)), ENT_QUOTES);
    $reply_url   = bbp_get_reply_url($reply_id);

    $subject = apply_filters('bbp_subscription_mail_title', '[' . $forum_title . '] ' . $topic_title, $reply_id, $topic_id);
    $message = sprintf(
        "%s の新しい返信:\n\n%s\n\nリンク: %s\n\n—\nこのメールはフォーラムまたはトピックを購読しているため届いています。購読解除はログイン後、該当ページから行えます。",
        $reply_name,
        $reply_body,
        $reply_url
    );
    $message = apply_filters('bbp_subscription_mail_message', $message, $reply_id, $topic_id);
    if (empty($subject) || empty($message)) return;

    $headers   = [ bbp_get_email_header() ];
    $no_reply  = bbp_get_do_not_reply_address();
    $from_mail = apply_filters('bbp_subscription_from_email', $no_reply);
    $headers[] = 'From: ' . get_bloginfo('name') . ' <' . $from_mail . '>';

    foreach (array_chunk($emails, 30) as $chunk) {
        wp_mail($chunk, $subject, $message, $headers);
    }
}