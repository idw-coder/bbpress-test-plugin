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
