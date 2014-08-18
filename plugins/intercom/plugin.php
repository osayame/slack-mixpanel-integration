<?php

	class intercom extends SlackServicePlugin {

		public $name = "Intercom";
		public $desc = "The easiest way to see and talk to your users.";

		public $cfg = array(
			'has_token' => true,
		);

		function onInit() {
			$channels = $this->getChannelsList();

			foreach ($channels as $k => $v) {
	            if ($v == '#testinghammock') {
	                $this->icfg['channel']      = $k;
	                $this->icfg['channel_name'] = $v;
	            }
        	}

        	$this->icfg['botname'] = "Intercom";
        	$this->icfg['icon_url'] = trim($GLOBALS['cfg']['root_url'], '/') . '/plugins/intercom/icon_48.png';
		}

		function onView() {
			return $this->smarty->fetch('view.txt');
		}

		function onEdit() {
			$channels = $this->getChannelsList();
				if ($_GET['save']) {
	            $this->icfg['channel']      = $_POST['channel'];
	            $this->icfg['channel_name'] = $channels[$_POST['channel']];
	            $this->icfg['botname']      = $_POST['botname'];
	            $this->saveConfig();

	            header("location: {$this->getViewUrl()}&saved=1");
	            exit;
	        }
	        $this->smarty->assign('channels', $channels);
	        return $this->smarty->fetch('edit.txt');
		}

		function onHook($request){

			if ($request['post']['payload']) {
				$payload = json_decode($request['post']['payload'], true);
			} else {
				$payload = json_decode($request['post_body'], true);
			}

			if (!$payload){
				return array('ok' => false, 'error' => "invalid_payload");
			}

			$message = $payload['text'];

			if ($payload['attachments']){
				$attachments = array();
				$i = 0;
				foreach ($payload['attachments'] as $a){
					foreach ($a as $key => $value){
						$attachments[$i][$key] = $value;
					}
					$i++;
				}
			}

			$this->postToChannel($message, array(
				'channel'		=> $this->icfg['channel'],
				'username'		=> $this->icfg['botname'],
				'icon_url'		=> $this->icfg['icon_url'],
				'attachments'	=> $attachments,
			));
		}

		function getLabel() {
        	return "Post updates to {$this->icfg['channel_name']} as {$this->icfg['botname']}";
    	}
	}