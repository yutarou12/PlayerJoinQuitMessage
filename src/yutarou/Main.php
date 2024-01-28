<?php

namespace yutarou;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\Player;

use pocketmine\utils\Config;

class Main extends PluginBase implements Listener {
    
    public function onEnable():void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        $this->getLogger()->info("§eプラグインを有効にしました");
        $this->getLogger()->info("§a作成者 yutarou1241477");

        if(!file_exists($this->getDataFolder())){mkdir($this->getDataFolder(), 0744, true);}
		$this->config = new Config($this->getDataFolder() . "message.yml", Config::YAML, array(
		    "鯖主ID" => "yutarou1241477",
		    "鯖主参加メッセージ" => "§l【鯖主】§6%nameさんがサーバーに参加しました",
		    "鯖主退出メッセージ" => "§l【鯖主】§7%nameさんがサーバーから退出しました §7(理由:%reason)",
		    "参加メッセージ" => "§l【鯖民】§a%nameさんがサーバーに参加しました",
		    "退出メッセージ" => "§l【鯖民】§7%nameさんがサーバーから退出しました §7(理由:%reason)",
		    "権限者参加メッセージ" => "§l【権限者】§a%nameさんがサーバーに参加しました",
		    "権限者退出メッセージ" => "§l【権限者】§7%nameさんがサーバーから退出しました §7(理由:%reason)",
		    "初参加メッセージ" => "§l【初参加】§6%nameさんが初めてサーバーに参加しました"
		));
		$this->reason = new Config($this->getDataFolder() . "reason.yml", Config::YAML, array(
			"timeout" => "タイムアウト",
			"client disconnect" => "切断",
			"Internal server error" => "Server Error"
		));
    }
  
	public function onJoin(PlayerJoinEvent $event){
	    $player = $event->getPlayer();
	    $name = $event->getPlayer()->getName();

	    $Owner = $this->config->get("鯖主ID");
	    $message = $this->config->get("参加メッセージ") ;
	    $messageOp = $this->config->get("権限者参加メッセージ");
	    $messageFirst = $this->config->get("初参加メッセージ");
	    $messageOwner = $this->config->get("鯖主参加メッセージ");
	    $message = str_replace("%name", $name, $message);
	    $messageOp = str_replace("%name", $name, $messageOp);
	    $messageFirst = str_replace("%name" , $name, $messageFirst);
	    $messageOwner = str_replace("%name" , $name, $messageOwner);
	    
	    if(!$player->hasPlayedBefore()){
	        $event->setJoinMessage($messageFirst);
	    }else if ($name == $Owner) {
	        $event->setJoinMessage($messageOwner);
	    }else if ($this->getServer()->isOp($name)){
	        $event->setJoinMessage($messageOp);
	    }else{
	        $event->setJoinMessage($message);
	    }
	}
	
	public function onQuit(PlayerQuitEvent $event){
	    $player = $event->getPlayer();
	    $name = $event->getPlayer()->getName();
	    $Owner = $this->config->get("鯖主ID");
	    $message = $this->config->get("退出メッセージ") ;
	    $messageOp = $this->config->get("権限者退出メッセージ");
	    $messageOwner = $this->config->get("鯖主退出メッセージ");
	    $message = str_replace("%name", $name, $message);
	    $messageOp = str_replace("%name", $name, $messageOp);
		$messageOwner = str_replace("%name" , $name, $messageOwner);

		$reason = $event->getQuitReason();
		$reasons = "その他";

		foreach($this->reason->getAll() as $key => $value){
			if(strpos($reason,$key) !== false){
				$reasons = $this->reason->get($key);
			}
		}
	    
	    if ($name == $Owner) {
			$messageOwner = str_replace("%reason", $reasons, $messageOwner);
	        $event->setQuitMessage($messageOwner);
	    }else if ($this->getServer()->isOp($name)){
			$messageOp = str_replace("%reason", $reasons, $messageOp);
	        $event->setQuitMessage($messageOp);
	    }else{
			$message = str_replace("%reason", $reasons, $message);
	        $event->setQuitMessage($message);
	    }
	}
	
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{
	    switch ($command->getName()){
	        case "msg_reload":
                $name = $sender->getName();
	            if($name == 'CONSOLE' or $this->getServer()->isOp($name)){
					$this->config->reload();
					$this->reason->reload();
					$sender->sendMessage("Configをリロードしました");
	            }
	            break;
		}
		return true;
	}
}