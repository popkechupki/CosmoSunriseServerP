<?php

namespace CoSSeSystem;

/*
Cosmo Sunrise Server's System.
Development start date: 2016/06/29

このプラグインはpopke LISENCEを理解および同意した上で使用する事。
また、無駄なコードはことごとく排除するよう書く事を心がける事。
*/

/*use文*/
//default
use pocketmine\Server;
use pocketmine\Player;
use pocketmine\event\Listener;
use pocketmine\utils\TextFormat as TF;
use pocketmine\utils\Config;
use pocketmine\plugin\PluginBase;
//event
use pocketmine\event\player\{PlayerJoinEvent, PlayerQuitEvent, PlayerDeathEvent, PlayerInteractEvent, PlayerGameModeChangeEvent};
use pocketmine\event\block\BlockBreakEvent;
//command
use pocketmine\command\{Command, CommandSender, ConsoleCommandSender};
//level
use pocketmine\level\Position;
//another
use pocketmine\item\Item;
use pocketmine\entity\DroppedItem;

class CosmoSunriseServerP extends PluginBase implements Listener{

    private $serverInstance;

	public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        $this->getLogger()->info(TF::GREEN."CosmoSunriseServerP is Enabled!");
        #private
        $this->serverInstance  = Server::getInstance();
        $server = $this->getServer();
        #config
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0740, true);
        $this->BTH = new Config($this->getDataFolder() . "BackToHome.yml", Config::YAML);
        $this->SETTING = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
        $this->NOTICE = new Config($this->getDataFolder() . "notice.yml", Config::YAML);
        if(!$this->SETTING->exists("LoadLevel")){
            $this->SETTING->set("LoadLevel",array("cosse003-live", "cosse-sigen", "2016/07/18"));
        }
        $this->BTH->save();
        $this->NOTICE->save();
        $live = $this->SETTING->get("LoadLevel")[0];
        $sigen = $this->SETTING->get("LoadLevel")[1];
        if(!$this->SETTING->exists($live)){
            $this->SETTING->set($live,array(128, 83, 128));
        }
        if(!$this->SETTING->exists($sigen)){
            $this->SETTING->set($sigen,array(128, 83, 128));
        }
        $this->SETTING->save();
        
        #APIs Load
        if($this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI") != null and $this->getServer()->getPluginManager()->getPlugin("BankofCoSSe") != null){
            $this->CMA = $this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI");
            $this->BCS = $this->getServer()->getPluginManager()->getPlugin("BankofCoSSe");
        }else{
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
        #level load
        if(!$server->isLevelLoaded($sigen)) return $this->getServer()->loadLevel($sigen);
        
    }
    
	function onPlayerJoin(PlayerJoinEvent $event){
		#dafault
		$player = $event->getPlayer();
		$user = $player->getName();
        $ps = count($this->getServer()->getOnlinePlayers());
		#JoinMessage
        $event->setJoinMessage("[§aJoin§f]".$user." joined the game. ({$ps}/10)");
		$player->sendMessage("§7x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x§r"."\n"."§aCosmoSunriseServerへようこそ！"."\n"."HP(mcpecosse.webcrow.jp)でルールは確認しましたか？"."\n"."このサーバーは開拓生活・経済サーバーです。"."\n"."詳しい登録方法はHP「サーバーへの参加方法」をご覧ください。"."\n"."§7x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x§r");
        #Notice
        if($this->NOTICE->exists($user)){
            $player->sendMessage("[§aNotice from CoSSe§f]"."\n"."あなた宛に通知が来ています。確認は「/notice read」");
        }
	}

	function onPlayerQuit(PlayerQuitEvent $event){
		$user = $event->getPlayer()->getName();
        $ps = count($this->getServer()->getOnlinePlayers());
		#QuitMessage
        $event->setQuitMessage("[§6Quit§f]".$user." left the game. ({$ps}/10)");
	}

	function PlayerDeath(PlayerDeathEvent $event){
        $event->setKeepInventory(true);
    }

    function PlayerBlockTouch(PlayerInteractEvent $event){
    	$player = $event->getPlayer();
        $user = $player->getName();
        $itemD = $event->getBlock()->getDamage();
        switch ($event->getBlock()->getId()) {
            
            case Item::MONSTER_SPAWNER:
                $arm = array($player->getInventory()->getHelmet()->getId(), $player->getInventory()->getChestplate()->getId(), $player->getInventory()->getLeggings()->getId(), $player->getInventory()->getBoots()->getId());
                $player->getInventory()->clearAll();
                $player->sendMessage("[§aCoSSe§f]"."\n"."§cインベントリ内のアイテムを消去しました。");
                $player->getInventory()->setArmorItem(0, Item::get($arm[0], 0, 1));
                $player->getInventory()->setArmorItem(1, Item::get($arm[1], 0, 1));
                $player->getInventory()->setArmorItem(2, Item::get($arm[2], 0, 1));
                $player->getInventory()->setArmorItem(3, Item::get($arm[3], 0, 1));
                $player->getInventory()->sendArmorContents($player);
                break;
            
            case Item::WOOL:
                $BANK = $this->BCS->getBCSAccount($user);
                $PMY = $this->CMA->getMoney($user);
                if ($itemD == 1) {//オレンジ
                    
                    $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."-あなたの所持金一覧-"."\n"."§cPocketMoney§f: §c{$PMY}cs"."\n"."§bBank of CoSSe§f: §b{$BANK}cs");
                } elseif($itemD == 2) {//赤紫
                    if($BANK >= 250) {
                        $this->BCS->addBCSAccount($user, -250);
                        $this->CMA->addMoney($user, + 250);
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの口座から250cs引き出しました。");
                    } else {
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの口座の残金が足りません。");
                    }
                } elseif($itemD == 3) {//空色
                    if($PMY >= 250) {
                        $this->BCS->addBCSAccount($user, +250);
                        $this->CMA->addMoney($user, -250);
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの口座に250cs入金しました。");
                    } else {
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの手持ちのお金が足りません。");
                    }
                }
                break;

        }
    }

    function onCommand(CommandSender $sender, Command $command, $label, array $args){
        #デフォ
        if (!$sender instanceof Player) return $this->getServer()->broadcastMessage("[§aCoSSe§f]"."\n"."§bサーバのメンテナンスを行います。再開までしばらくお待ちください。");
            $player = $sender->getPlayer();
    	    $user = $player->getName();
        #プライーベート
        //$Server = $this->server;
        #レヴェル
    	$level = $player->getLevel()->getFolderName();
    	$live = $this->SETTING->get("LoadLevel")[0];
    	$source = $this->SETTING->get("LoadLevel")[1];
        $day = $this->SETTING->get("LoadLevel")[2];
        $x = round($player->x, 1);
        $y = round($player->y, 1);
        $z = round($player->z, 1);
        #/info 2
        if($this->NOTICE->exists($user)){
            $n = "通知が届いています。/readで確認してください。";
        }else{
            $n = "あなたに通知は届いていません。";
        }
        #そのた
        $time = date("Y年m月j日 H時i分s秒");
        $PMY = $this->CMA->getMoney($user);
    	switch (strtolower($command->getName())){
    		case 'info':
    			if(!$sender instanceof Player){
    				$this->getLogger()->warning("You can use this command only use in the game.");
    			}else{	
    				if(isset($args[0])){
                        switch ($args[0]) {
                            case '1':
                                $sender->sendMessage("[§aCoSSe§f]"."\n"."-あなたの座標情報-"."\n"."§cX§f: §c{$x}"."\n"."§bY§f: §b{$y}"."\n"."§aZ§f: §a{$z}"."\n"."§eworld§f: §e".$level."\n"."§d備考§f:"."\n"." -生活ワールド: §dy50以下の採掘を制限しています。"."\n"." -資源ワールド: §d{$day}にリセットされます。");
                                break;

                            case '2':
                                $sender->sendMessage("[§aCoSSe§f]"."\n"."-現在時間-"."\n"."§b".$time."\n"."-所持金-"."\n".TF::GOLD.$PMY."cs"."\n"."-通知-"."\n"."§e".$n);
                                break;

                            case '3':
                                $sender->sendMessage("[§aCoSSe§f]"."\n"."-ほしリスト-"."\n"."§e☆§f: サーバー管理者"."\n"."§c☆§f: サーバー副管理者"."\n"."§b☆§f: サーバー警察"."\n"."§a☆§f: プラグイン製作・提供者"."\n"."§9☆§f: CoSSeのプレイヤー印");
                                break;
                            }
                        }else{
                            $sender->sendMessage("[§aCoSSe§f]"."\n"."/info <ページナンバー>"."\n"."-infoコマンドのページナンバー-"."\n"."1: ワールド情報"."\n"."2: 時間、所持金"."\n"."3: 名前の星確認");
                        }
                    }
    			break;

    		case 'owner':
    			if (!isset($args[0])) {
    				$sender->sendMessage("0: help, 1: backup, 2: update, 3: itemclear, 4:notice");
    			}elseif($args[0] == 1){
    				$this->getServer()->broadcastMessage("[§aCoSSe§f]"."\n"."§bVPSのバックアップを行います。再開までしばらくお待ちください。");
    			}elseif($args[0] == 2){
    				$this->getServer()->broadcastMessage("[§aCoSSe§f]"."\n"."§b要素追加またはバグ修正を行います。再開までしばらくお待ちください。");
    			}elseif($args[0] == 3){
                    /*$entity = $sender;
    			    foreach($this->getServer()->$this->getServer()->getLevelByName($level)->getEntities() as $entity){
                        if($entity instanceof Item || $entity instanceof DroppedItem){
                        	$entity->kill();
                        }elseif($entity instanceof XPOrb){
                        	$entity->close();
                        }
                    $this->getServer()->broadcastMessage("[§aCoSSe§r]§bドロップしているアイテムを消去しました。");
                	}*/
    			}elseif($args[0] == 4){
                    $this->NOTICE->set($args[1], $args[2]);
                    $this->NOTICE->save();
                    $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."通知を送信しました。");
                }
    			break;

    		case 'food':
    			if($PMY < 100){
    				$sender->sendMessage("[§aCoSSe§f]"."\n"."§bお金がたりません...");
    			}else{
    				$item = Item::get(364, 0, 10);
    				$player->getInventory()->addItem($item);
    				$this->CMA->addMoney($user, -100);
    				$sender->sendMessage("[§aCoSSe§f]"."\n"."調理したステーキを10個販売しました。");
    			}
    			break;

    		case 'world':
                if($level == $source){
                    $sender->teleport(new Position($this->SETTING->get($live)[0], $this->SETTING->get($live)[1], $this->SETTING->get($live)[2], $this->getServer()->getLevelByName($live)));
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."§b生活ワールドにワープしました！");
                }elseif($level == $live){
                    $sender->teleport(new Position($this->SETTING->get($source)[0], $this->SETTING->get($source)[1], $this->SETTING->get($source)[2], $this->getServer()->getLevelByName($source)));
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."§b資源ワールドにワープしました！");
                }
                break;

            case 'sethome':
                $this->BTH->set($user, array($x, $y, $z, $level));
                $this->BTH->save();
                $sender->sendMessage("[§aCoSSe§f]"."\n"."あなたのテレポート先を登録しました。 /homeで戻れます。");
                break;

            case 'home':
                if($this->BTH->exists($user)){
                    $x = $this->BTH->get($user)[0];
                    $y = $this->BTH->get($user)[1];
                    $z = $this->BTH->get($user)[2];
                    $level = $this->BTH->get($user)[3];
                    $pos = new Position($x, $y, $z, $this->getServer()->getLevelByName($level));
                    $sender->teleport($pos);
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."設定された座標にテレポートしました。");
                }else{
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."あなたのテレポート先の座標が設定されていません。 /sethomeで設定してください。");
                }
                break;

            case 'delhome':
                if($this->BTH->exists($user)){
                    $this->BTH->remove($user);
                    $this->BTH->save();
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."あなたのテレポート先の座標を削除しました。");
                }else{
                    $sender->sendMessage("[§aCoSSe§f]"."\n"."あなたのテレポート先は設定されていません。 /sethomeで設定してください。");
                }
                break;

            case 'notice':
                if (!isset($args[0])) {
                    $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."/notice <read | del>");
                }else{
                    if($args[0] == "read"){
                        if($this->NOTICE->exists($user)){
                            $message = $this->NOTICE->get($user);
                            $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."通知: ".$message."\n"."これを確認した後「/bank n del」で通知を消去することをお勧めします。"); 
                        }else{
                            $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."あなたには通知が来ていません。");
                        }
                    }elseif($args[0] == "del"){
                        $this->NOTICE->remove($user);
                        $this->NOTICE->save();
                        $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."通知を消去しました。");
                    }
                }
                break;
    	}
    }

    function onPlayerBlockBreak(BlockBreakEvent $event){
    	$player = $event->getPlayer();
    	$user = $player->getName();
        $level = $player->getLevel()->getFolderName();
        $live = $this->SETTING->get("LoadLevel")[0];
        if($event->getBlock()->y <= 50 and $level == $live){
            if(!$this->SETTING->exists($user)){
                $event->setCancelled();
                $player->sendMessage("[§aCoSSe§f]"."\n"."§bY50以下のため採掘ができません。詳細は「/info 1」");
            }
        }else{
            switch ($event->getBlock()->getId()) {
                case"1":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"2":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"3":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"4":
                    $this->BCS->addBCSAccount($user, +10);
                    break;
                
                case"5":
                    $this->BCS->addBCSAccount($user, +10);
                    break;
                
                case"12":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"13":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"14":
                    $this->BCS->addBCSAccount($user, +30);
                    break;
                
                case"15":
                    $this->BCS->addBCSAccount($user, +30);
                    break;
                
                case"16":
                    $this->BCS->addBCSAccount($user, +20);
                    break;
                
                case"17":
                    $this->BCS->addBCSAccount($user, +20);
                    break;
                
                case"18":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"21":
                    $this->BCS->addBCSAccount($user, +25);
                    break;
                
                case"24":
                    $this->BCS->addBCSAccount($user, +10);
                    break;
                
                case"56":
                    $this->BCS->addBCSAccount($user, +100);
                    break;
                
                case"59":
                    $this->BCS->addBCSAccount($user, +5);
                    break;
                
                case"73":
                
                case"74":
                    $this->BCS->addBCSAccount($user, +25);
                    break;
                
                case"80":
                    $this->BCS->addBCSAccount($user, +10);
                    break;
                
                case"82":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"86":
                    $this->BCS->addBCSAccount($user, +5);
                    break;
                
                case"87":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"88":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
                
                case"89":
                    $this->BCS->addBCSAccount($user, +25);
                    break;
                
                case"103":
                    $this->BCS->addBCSAccount($user, +2);
                    break;
            }
        }
    }

    function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
        $player = $event->getPlayer();
        $user = $player->getName();
        if($player->isOP()){
                $player->sendMessage("[§aCoSSe: 認証§f]"."\n"."§bあなたがOPであることが確認されました。");
            }else{
                $this->getServer()->broadcastMessage("[§cCoSSe: 警告§f]"."\n".$user."§bがゲームモードを変更しようとしたのでKickしました。");
                $event->setCancelled();
                $player->kick("OPでないのにゲームモードを変更しようとしたためkickしました。", false);
            }
    }
}