<?php

if(isset($_POST['function'])){
	$function = filter_var($_POST['function'], FILTER_SANITIZE_STRING);
	$log = array();
    
	switch($function) {
		
		case('updateCount'):
			global $wpdb;
			$author_chat_table = $wpdb->prefix . 'author_chat';
			$linesCount = $wpdb->get_var("SELECT COUNT(*) FROM $author_chat_table");
			$log = $linesCount;
		break;
	    
		case('getState'):
			global $wpdb;
			$author_chat_table = $wpdb->prefix . 'author_chat';
			$newLinesCount = $wpdb->get_var("SELECT COUNT(*) FROM $author_chat_table");
			$log = $newLinesCount;
		break;
		
		case('send'):
			$nickname = strip_tags(filter_var($_POST['nickname'], FILTER_SANITIZE_STRING));
			$reg_exUrl = "/(http|https|ftp|ftps)\:\/\/[a-zA-Z0-9\-\.]+\.[a-zA-Z]{2,3}(\/\S*)?/";
			$message = strip_tags(filter_var($_POST['message'], FILTER_SANITIZE_STRING));
			if(($message) != "\n"){
				if(preg_match($reg_exUrl, $message, $url)) {
					$message = preg_replace($reg_exUrl, '<a href="'.$url[0].'" target="_blank">'.$url[0].'</a>', $message);
				}
				global $wpdb;
				$author_chat_table = $wpdb->prefix . 'author_chat';
				$wpdb->query($wpdb->prepare(
					"INSERT INTO $author_chat_table (nickname, content, date) VALUES (%s, %s, NOW())",
					$nickname,
					$message
					));
			}
		break;
		
		case('update'):
			global $wpdb;
			$author_chat_table = $wpdb->prefix . 'author_chat';
			$lines = $wpdb->get_results("SELECT nickname, content, date FROM $author_chat_table ORDER BY id ASC", ARRAY_A);
				$text = array();
				foreach ($lines as $line){
						$text[] = $line;
				}
			$log = array_column($text, 'nickname');
			$log2 = array_column($text, 'content');
			$log3 = array_column($text, 'date');
			array_walk_recursive($log3, function(&$element){
				$element = strtotime($element);
				$element = date('j-m-Y </\s\p\a\n> <\s\p\a\n \i\d="\t\i\m\e">G:i:s', $element);
			});
		break;

		case('initiate'):
				global $wpdb;
				$author_chat_table = $wpdb->prefix . 'author_chat';
				$lines = $wpdb->get_results("SELECT nickname, content, date FROM $author_chat_table ORDER BY id ASC", ARRAY_A);
				$text = array();
				foreach ($lines as $line){
					$text[] = $line;
				}
				$log = array_column($text, 'nickname');
				$log2 = array_column($text, 'content');
				$log3 = array_column($text, 'date');
				array_walk_recursive($log3, function(&$element){
					$element = strtotime($element);
					$element = date('j-m-Y </\s\p\a\n> <\s\p\a\n \i\d="\t\i\m\e">G:i:s', $element);
				});
		break;
	}
	if(isset($log2)){
		echo wp_send_json(array('result1'=>$log, 'result2'=>$log2, 'result3'=>$log3));
	}else{
		echo wp_send_json($log);
	}

}

?>