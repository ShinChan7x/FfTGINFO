<?php
$bot_token = "7592894356:AAHMklcnPTSOz6Ay0l7Gps4W-yIfou_EafU";
$allowed_group_id = -1002881479162;
$api_url = "https://api.telegram.org/bot$bot_token/";

// Parse the incoming message
$content = file_get_contents("php://input");
$update = json_decode($content, true);

if (!isset($update["message"])) exit;

$message = $update["message"];
$chat_id = $message["chat"]["id"];
$text = trim($message["text"] ?? "");
$chat_type = $message["chat"]["type"] ?? "";
$from = $message["from"]["id"];
$username_tag = "";

// ✅ Group Check
if (!in_array($chat_type, ["group", "supergroup"])) exit;
if ($chat_id != $allowed_group_id) exit;

// ✅ Command Parse
if (strpos($text, "/check") === 0) {
    $parts = explode(" ", $text);

    foreach ($parts as $key => $part) {
        if (strpos($part, "#") === 0 && is_numeric(substr($part, 1))) {
            $username_tag = $part;
            unset($parts[$key]);
            break;
        }
    }

    if (count($parts) !== 3) {
        sendMessage($chat_id, "Usage: /check bd 7842525752 #user_id", $api_url);
        exit;
    }

    $region = $parts[1];
    $uid = $parts[2];
    $url = "https://freefireinfo.nepcoderapis.workers.dev/?uid=$uid&region=$region";

    // Fetch from API
    $player_data = json_decode(file_get_contents($url), true);

    if (!$player_data || !isset($player_data["AccountInfo"])) {
        sendMessage($chat_id, "❌ Error fetching player data.", $api_url);
        exit;
    }

    $acc = $player_data["AccountInfo"];
    $guild = $player_data["GuildInfo"] ?? [];
    $pet = $player_data["petInfo"] ?? [];
    $social = $player_data["socialinfo"] ?? [];

    function readableTime($ts) {
        return date("d-m-Y H:i:s", $ts);
    }

    $msg = "$username_tag\n";
    $msg .= "<b>👤 Player Info</b>\n";
    $msg .= "<b>Name:</b> " . htmlspecialchars($acc["AccountName"]) . "\n";
    $msg .= "<b>UID:</b> $uid\n";
    $msg .= "<b>Region:</b> " . $acc["AccountRegion"] . "\n";
    $msg .= "<b>Level:</b> " . $acc["AccountLevel"] . "\n";
    $msg .= "<b>EXP:</b> " . number_format($acc["AccountEXP"]) . "\n";
    $msg .= "<b>Likes:</b> " . $acc["AccountLikes"] . "\n";
    $msg .= "<b>Created At:</b> " . readableTime($acc["AccountCreateTime"]) . "\n";
    $msg .= "<b>Last Login:</b> " . readableTime($acc["AccountLastLogin"]) . "\n\n";

    $msg .= "<b>🔥 Rank & Stats</b>\n";
    $msg .= "<b>BR Rank:</b> " . ($acc["BrRankPoint"] ?? "N/A") . "\n";
    $msg .= "<b>CS Rank:</b> " . ($acc["CsRankPoint"] ?? "N/A") . "\n";
    $msg .= "<b>Badges:</b> " . ($acc["AccountBPBadges"] ?? 0) . "\n";
    $msg .= "<b>Elite Pass:</b> " . (($acc["DiamondCost"] ?? 0) > 0 ? "Yes" : "No") . "\n\n";

    $msg .= "<b>🏳️ Guild Info</b>\n";
    $msg .= "<b>Name:</b> " . ($guild["GuildName"] ?? "N/A") . "\n";
    $msg .= "<b>Owner:</b> " . ($guild["GuildOwner"] ?? "N/A") . "\n";
    $msg .= "<b>Members:</b> " . ($guild["GuildMember"] ?? 0) . " / " . ($guild["GuildCapacity"] ?? 0) . "\n";
    $msg .= "<b>Level:</b> " . ($guild["GuildLevel"] ?? "N/A") . "\n\n";

    $msg .= "<b>🐾 Pet Info</b>\n";
    $msg .= "<b>ID:</b> " . ($pet["id"] ?? "N/A") . "\n";
    $msg .= "<b>Level:</b> " . ($pet["level"] ?? "N/A") . "\n";
    $msg .= "<b>Skin:</b> " . ($pet["skinId"] ?? "N/A") . "\n";
    $msg .= "<b>Skill:</b> " . ($pet["selectedSkillId"] ?? "N/A") . "\n\n";

    $msg .= "<b>🌐 Social</b>\n";
    $msg .= "<b>Gender:</b> " . str_replace("Gender_", "", $social["Gender"] ?? "N/A") . "\n";
    $msg .= "<b>Language:</b> " . str_replace("Language_", "", $social["AccountLanguage"] ?? "N/A") . "\n";
    $msg .= "<b>Mode:</b> " . str_replace("ModePrefer_", "", $social["ModePreference"] ?? "N/A") . "\n";
    $msg .= "<b>Signature:</b> " . strip_tags(str_replace(["[B]", "[C]"], "", $social["AccountSignature"] ?? "")) . "\n";

    sendMessage($chat_id, $msg, $api_url);
}

function sendMessage($chat_id, $text, $api_url) {
    $params = [
        "chat_id" => $chat_id,
        "text" => $text,
        "parse_mode" => "HTML"
    ];
    file_get_contents($api_url . "sendMessage?" . http_build_query($params));
}
?>
