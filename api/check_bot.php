<?php
$bot_token = "7592894356:AAHMklcnPTSOz6Ay0l7Gps4W-yIfou_EafU";
$allowed_group_id = -1002881479162;

$content = file_get_contents("php://input");
$update = json_decode($content, true);
if (!$update) exit();

$message = $update["message"] ?? null;
if (!$message || $message["chat"]["id"] != $allowed_group_id || strpos($message["text"], "/check") !== 0) exit();

$args = explode(" ", $message["text"]);
$user_tag = "";
foreach ($args as $index => $arg) {
    if (strpos($arg, "#") === 0 && is_numeric(substr($arg, 1))) {
        $user_tag = $arg;
        unset($args[$index]);
        break;
    }
}
$args = array_values($args);
if (count($args) < 3) {
    send_reply($message["chat"]["id"], $message["message_id"], "Usage: /check bd 7842525752 #user_id");
    exit();
}

$region = $args[1];
$uid = $args[2];

send_reply($message["chat"]["id"], $message["message_id"], "🔍 Fetching player data...");

$url = "https://freefireinfo.nepcoderapis.workers.dev/?uid=$uid&region=$region";
$response = file_get_contents($url);
$data = json_decode($response, true);
if (!$data) {
    send_reply($message["chat"]["id"], $message["message_id"], "❌ Error fetching info.");
    exit();
}

$acc = $data["AccountInfo"] ?? [];
$guild = $data["GuildInfo"] ?? [];
$pet = $data["petInfo"] ?? [];
$social = $data["socialinfo"] ?? [];

function u($ts) {
    return is_numeric($ts) ? date("d-m-Y H:i:s", $ts) : "N/A";
}

$text = "$user_tag
<b>👤 Player Info</b>
<b>Name:</b> {$acc["AccountName"]}
<b>UID:</b> $uid
<b>Region:</b> {$acc["AccountRegion"]}
<b>Level:</b> {$acc["AccountLevel"]}
<b>EXP:</b> " . number_format($acc["AccountEXP"] ?? 0) . "
<b>Likes:</b> {$acc["AccountLikes"]}
<b>Created At:</b> " . u($acc["AccountCreateTime"]) . "
<b>Last Login:</b> " . u($acc["AccountLastLogin"]) . "

<b>🔥 Rank & Stats</b>
<b>BR Rank:</b> {$acc["BrRankPoint"]}
<b>CS Rank:</b> {$acc["CsRankPoint"]}
<b>Badges:</b> {$acc["AccountBPBadges"]}
<b>Elite Pass:</b> " . (($acc["DiamondCost"] ?? 0) > 0 ? "Yes" : "No") . "

<b>🏳️ Guild Info</b>
<b>Name:</b> {$guild["GuildName"]}
<b>Owner:</b> {$guild["GuildOwner"]}
<b>Members:</b> {$guild["GuildMember"]} / {$guild["GuildCapacity"]}
<b>Level:</b> {$guild["GuildLevel"]}

<b>🐾 Pet Info</b>
<b>ID:</b> {$pet["id"]}
<b>Level:</b> {$pet["level"]}
<b>Skin:</b> {$pet["skinId"]}
<b>Skill:</b> {$pet["selectedSkillId"]}

<b>🌐 Social</b>
<b>Gender:</b> " . str_replace("Gender_", "", $social["Gender"] ?? "N/A") . "
<b>Language:</b> " . str_replace("Language_", "", $social["AccountLanguage"] ?? "N/A") . "
<b>Mode:</b> " . str_replace("ModePrefer_", "", $social["ModePreference"] ?? "N/A") . "
<b>Signature:</b> " . trim(str_replace("[B][C]", "", $social["AccountSignature"] ?? "")) . "
";

send_reply($message["chat"]["id"], $message["message_id"], $text);

function send_reply($chat_id, $reply_to, $text) {
    global $bot_token;
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    $data = [
        "chat_id" => $chat_id,
        "reply_to_message_id" => $reply_to,
        "text" => $text,
        "parse_mode" => "HTML"
    ];
    file_get_contents($url . "?" . http_build_query($data));
}
?>
