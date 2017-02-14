<?php
// including the library
require("lib/telegram.php");

// if already configured on config.php file, delete/comment following lines
$TELEGRAM_BOTNAME = "lenabot";
$TELEGRAM_TOKEN = "...";
$STATUS_ENABLE = false;

// basic configuration
$singletrigger = true; // if true, it tells the library to trigger at most a single callback function for each received message

function inline_keyboard($link){
	$keyboard = new InlineKeyboardMarkup();
	$options[0][0]['text'] = "Try the Button";
    $options[0][0]['callback_data'] = "lena";
    $options[0][1]['text'] = "Demo Bot";
    $options[0][1]['url'] = $link;
    $keyboard->add_option($options);
    
    return json_encode($keyboard);
}

function trigger_welcome($p) {
    try {
        $answer = "Welcome...";
        $p->bot()->send_message($p->chatid(), $answer);
        return logarray('text', $answer);
    }
    catch(Exception $e) { return false; } // you can also return what you prefer
}

function trigger_help($p) {
    try {
        $answer = "Try /lena to get a picture of Lena.";
        $p->bot()->send_message($p->chatid(), $answer);
        return logarray('text', $answer);
    }
    catch(Exception $e) { return false; }
}

function trigger_photo($p) {
    try {
        $pic = "lena.jpg";
        $caption = "Look at Lena's picture!";
        $p->bot()->send_photo($p->chatid(), "$pic", $caption, null, inline_keyboard("https://github.com/auino/php-telegram-bot-library"));
        return logarray('photo', "[$pic] $caption"); // you choose the format you prefer
    }
    catch(Exception $e) { return false; }
}


$bot = new telegram_bot($TELEGRAM_TOKEN);

$data = $bot->read_post_message();

$query = @$data->callback_query;
$chatid = @$query->message->chat->id;
$message_id = @$query->message->message_id;
$date = @$query->message->date;
$text = @$query->data;

//answer to query
if(!is_null($chatid)){
	@db_log($TELEGRAM_BOTNAME, 'recv', $chatid, 'text', $text, $date);

	if($text == "lena"){
				$pic = "lena.jpg";
				$caption = "Send from a Button";
				$bot->edit_caption($chatid, $message_id);
				$bot->edit_replymarkup($chatid, $message_id);
				$bot->send_photo($chatid, "$pic", $caption, null, inline_keyboard("https://github.com/auino/php-telegram-bot-library"));
				//return logarray('photo', "[$pic] $caption"); // you choose the format you prefer
	}
	exit;
}



$message = $data->message;
$date = $message->date;
$chatid = $message->chat->id;
$text = @$message->text;

@db_log($TELEGRAM_BOTNAME, 'recv', $chatid, 'text', $text, $date);
//define Trigger set
$ts = new telegram_trigger_set($TELEGRAM_BOTNAME, $chatid, true);

//Register Trigger
$ts->register_trigger_text_command("trigger_welcome", ["/start","/welcome","/hi"], 0);
$ts->register_trigger_text_command("trigger_help",["/help"], -1);
$ts->register_trigger_text_command("trigger_photo", ["/lena"], 0);

//Run Trigger automatical
$response = $ts->run($bot, $message);


//Logging
@db_log($TELEGRAM_BOTNAME, 'send', $chatid, $response[0]["type"], $response[0]["content"], $date);

?>
