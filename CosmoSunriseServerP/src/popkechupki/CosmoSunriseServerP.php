<?php

namespace popkechupki;

/*
Cosmo Sunrise Server's System.
Development start date: 2016/06/29
Last up date: 2016/07/15

このプラグインはCosmoSunriseServerでのみ使用可能なものとする。
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
use pocketmine\inventory\PlayerInventory;
use pocketmine\inventory\BigShapedRecipe;

class CosmoSunriseServerP extends PluginBase implements Listener{

    private $server;
    
    const Message = TF::WHITE."[".TF::GREEN."CoSSe".TF::WHITE."]";
	
    public function onEnable() {
	   #private $server
        $server = Server::getInstance();
        $this->server = $server;
        #craftitem
        $server->getCraftingManager()->registerRecipe(((new BigShapedRecipe(Item::get(98, 0, 4),
            "N N",
            "   ",
            "N N"
        ))->setIngredient("N", Item::get(1, 0, 2))));
        $server->getCraftingManager()->registerRecipe(((new BigShapedRecipe(Item::get(109, 0, 4),
            "N  ",
            "NN ",
            "NNN"
        ))->setIngredient("N", Item::get(98, 0, 1))));
        $server->getCraftingManager()->registerRecipe(((new BigShapedRecipe(Item::get(44, 5, 6),
            "N  ",
            "   ",
            "   "
        ))->setIngredient("N", Item::get(98, 0, 1))));
        $this->getServer()->getPluginManager()->registerEvents($this,$this);
        #config make
        if (!file_exists($this->getDataFolder())) @mkdir($this->getDataFolder(), 0740, true);
        $this->BTH = new Config($this->getDataFolder() . "BackToHome.yml", Config::YAML);
        $this->SETTING = new Config($this->getDataFolder() . "setting.yml", Config::YAML);
        $this->NOTICE = new Config($this->getDataFolder() . "notice.yml", Config::YAML);
        if(!$this->SETTING->exists("LoadLevel")){
            $this->SETTING->set("LoadLevel",array("cosse003-live", "cosse-sigen", "2016/07/18"));
        }
        $this->SETTING->save();
        $live = $this->SETTING->get("LoadLevel")[0];
        $sigen = $this->SETTING->get("LoadLevel")[1];
        if(!$this->SETTING->exists($live)){
            $this->SETTING->set($live,array(128, 83, 128));
        }
        if(!$this->SETTING->exists($sigen)){
            $this->SETTING->set($sigen,array(128, 83, 128));
        }
        $this->SETTING->save();
        #leveload
        if(!$server->isLevelLoaded($this->getServer()->getLevelByName($sigen))) return $this->getServer()->loadLevel($sigen);
		/*API Load*/
        if($this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI") != null){
            $this->CoSSeMoney = $this->getServer()->getPluginManager()->getPlugin("CoSSeMoneyAPI");
        }elseif($this->getServer()->getPluginManager()->getPlugin("BankofCoSSe") != null){
            $this->BCS = $this->getServer()->getPluginManager()->getPlugin("BankofCoSSe");
        }else{
            $this->getLogger()->warning("APIが見つかりませんでした。プラグインを無効化します。");
            $this->getServer()->getPluginManager()->disablePlugin($this);
        }
    }
            
	function onPlayerJoin(PlayerJoinEvent $event){
		#dafault
		$player = $event->getPlayer();
		$user = $player->getName();
		#JoinTip
		$event->setJoinMessage("");
		$this->getServer()->broadcasttip($user."§a§oさんが参加しました。§r");
		#JoinMessage
		$player->sendMessage(TF::GRAY."x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x"."\n".TF::GREEN."CosmoSunriseServerへようこそ！"."\n"."HP(mcpecosse.webcrow.jp)でルールは確認してから参加してください。"."\n"."このサーバーは開拓生活・経済サーバーです。"."\n"."詳しい登録方法はHP「サーバーへの参加方法」をご覧ください。"."\n".TF::GRAY."x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x-x");
        #Notice
        if($this->NOTICE->exists($user)){
            $player->sendMessage(self::Messsage."\n"."あなた宛に通知が来ています。確認は「/notice read」");
        }
		#setStar
		switch ($user) {
			case 'popkechupki':
            	$player->setDisplayName("§9☆§e☆§a☆§f>".$user);
				break;
			
			case 'SASAMISAN':
				$player->setDisplayName("§9☆§c☆§f>".$user);
				break;

			case 'Splendente':
                $player->setDisplayName("§9☆§c☆§f>".$user);
				break;

			case 'Hayato8810':
				$player->setDisplayName("§9☆§b☆§f>".$user);
				break;

			case 'masasi650':
				$player->setDisplayName("§9☆§b☆§f>".$user);
				break;

			case 'keldeer':
				$player->setDisplayName("§9☆§b☆§f>".$user);
				break;

			case 'famimaS':
				$player->setDisplayName("§9☆§a☆§f>".$user);
				break;

            default:
                $player->setDisplayName("§9☆§f>".$user);
                break;
		}
		$player->save();
	}

	function onPlayerQuit(PlayerQuitEvent $event){
		$user = $event->getPlayer()->getName();
		#QuitTip
		$event->setQuitMessage("");
		$this->getServer()->broadcasttip($user."§a§oさんが退出しました。§r");
	}

	function PlayerDeath(PlayerDeathEvent $event){
        $event->setKeepInventory(true);
    }

    function PlayerBlockTouch(PlayerInteractEvent $event){
    	$player = $event->getPlayer();
        $user = $player->getName();
        $item = $event->getBlock()->getDamage();
        switch ($event->getBlock()->getId()) {
            
            case '52':
                $id_helmet = $player->getInventory()->getHelmet()->getId();
                $id_chestplate = $player->getInventory()->getChestplate()->getId();
                $id_leggins = $player->getInventory()->getLeggings()->getId();
                $id_boots = $player->getInventory()->getBoots()->getId();
                $player->getInventory()->clearAll();
                $player->sendMessage(self::Message."\n"."§cインベントリ内のアイテムを消去しました。");
                $player->getInventory()->setArmorItem(0, Item::get($id_helmet, 0, 1));
                $player->getInventory()->setArmorItem(1, Item::get($id_chestplate, 0, 1));
                $player->getInventory()->setArmorItem(2, Item::get($id_leggins, 0, 1));
                $player->getInventory()->setArmorItem(3, Item::get($id_boots, 0, 1));
                $player->getInventory()->sendArmorContents($player);
                break;
            
            case '35':
                if ($item == 1) {//オレンジ
                    $BCS = $this->BCS->get($user);
                    $PMY = $this->CoSSeMoney->getMoney($user);
                    $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."-あなたの所持金一覧-"."\n"."§cYourMoney§f: §c{$PMY}cs"."\n"."§bBank of CoSSe§f: §b{$BCS}cs");
                } elseif($item == 2) {//赤紫
                    if($this->BCS->get($user) >= 250) {
                        $amount = $this->BCS->get($user) - 250;
                        $this->BCS->set($user, $amount);
                        $this->CoSSeMoney->addMoney($user, +250);
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの口座から250cs引き出しました。");
                    } else {
                        $player->sendMessage("[§9Bank of CoSSe§f]"."\n"."あなたの口座の残金が足りません。");
                    }
                } elseif($item == 3) {//空色
                    if($this->CoSSeMoney->getMoney($user) >= 250) {
                        $amount = $this->BCS->get($user) + 250;
                        $this->BCS->set($user, $amount);
                        $this->CoSSeMoney->addMoney($user, -250);
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
        if (!$sender instanceof Player) return $this->getServer()->broadcastMessage(self::Message."\n"."§bサーバのメンテナンスを行います。再開までしばらくお待ちください。");
        $player = $sender->getPlayer();
        $user = $player->getName();
        $money = $this->CoSSeMoney->getMoney($user);
        #プライーベート
        $server = $this->server;
        #レヴェル
        //プレイヤーのいるワールド名
    	$level = $player->getLevel()->getFolderName();
    	//生活ワールド名取得
    	$live = $this->SETTING->get("LoadLevel")[0];
    	//資源ワールド名取得
    	$sigen = $this->SETTING->get("LoadLevel")[1];
    	//資源ワールドリセット予定日取得
        $day = $this->SETTING->get("LoadLevel")[2];
        //プレイヤーの座標を取得
        $x = round($player->x, 1);
        $y = round($player->y, 1);
        $z = round($player->z, 1);
        //生活ワールドのテレポート先指定
        $lx = $this->SETTING->get($live)[0];
        $ly = $this->SETTING->get($live)[1];
        $lz = $this->SETTING->get($live)[2];
        //資源ワールドのテレポート先取得
        $sx = $this->SETTING->get($sigen)[0];
        $sy = $this->SETTING->get($sigen)[1];
        $sz = $this->SETTING->get($sigen)[2];
        //ワールド名によって/info 1の備考欄の表記を変更
        if($level == $live){
            $bikou = "y50以下の採掘を制限しています。";
        }elseif($level == $sigen){
            $bikou = "{$day}にリセットされます。";
        }
        #時間を取得
        $time = date("Y年m月j日 H時i分s秒");
        
    	switch (strtolower($command->getName())){
    		case 'info':
    			if(!$sender instanceof Player){
    				$this->getLogger()->warning("You can use this command only use in the game.");
    			}else{	
    				
                    switch ($args[0]) {
                        case '1':
                            $sender->sendMessage(self::Message."\n"."-あなたと".$level."の座標情報-"."\n"."§cX§f: §c{$x}"."\n"."§bY§f: §b{$y}"."\n"."§aZ§f: §a{$z}"."\n"."§eworld§f: §e".$level."\n"."§d備考§f: §d".$bikou);
                            break;

                        case '2':
                            $sender->sendMessage(self::Message."\n"."-現在時間-"."\n"."§b".$time."\n"."-所持金-"."\n".TextFormat::GOLD.$money."cs"."\n"."-通知-"."\n"."§e/readで読むことができます。");
                            break;

                        case '3':
                            $sender->sendMessage(self::Message."\n"."-ほしリスト-"."\n"."§e☆§f: サーバー管理者"."\n"."§c☆§f: サーバー副管理者"."\n"."§b☆§f: サーバー警察"."\n"."§a☆§f: プラグイン製作・提供者"."\n"."§9☆§f: CoSSeのプレイヤー印");
                            break;
                        
                        default:
                            $sender->sendMessage(self::Message."\n"."/info <ページナンバー>"."\n"."-infoコマンドのページナンバー-"."\n"."1: ワールド情報"."\n"."2: 時間、所持金"."\n"."3: 名前の星確認");
                            break;
                    }
        		}
    			break;

    		case 'owner':
    			if (!isset($args[0])) {
    				$sender->sendMessage("0: help, 1: backup, 2: update, 3: itemclear, 4:notice");
    			}elseif($args[0] == 1){
    				$this->getServer()->broadcastMessage(self::Message."\n"."§bVPSのバックアップを行います。再開までしばらくお待ちください。");
    			}elseif($args[0] == 2){
    				$this->getServer()->broadcastMessage(self::Message."\n"."§b要素追加またはバグ修正を行います。再開までしばらくお待ちください。");
    			}elseif($args[0] == 3){
                    $entity = $sender;
    			    foreach($this->getServer()->$this->getServer()->getLevelByName($level)->getEntities() as $entity){
                        if($entity instanceof Item || $entity instanceof DroppedItem){
                        	$entity->kill();
                        }elseif($entity instanceof XPOrb){
                        	$entity->close();
                        }
                    $this->getServer()->broadcastMessage(self::Message."§bドロップしているアイテムを消去しました。");
                	}
    			}elseif($args[0] == 4){
                    $this->NOTICE->set($args[1], $args[2]);
                    $this->NOTICE->save();
                    $sender->sendMessage("[§aNotice from CoSSe§f]"."\n"."通知を送信しました。");
                }
    			break;

    		case 'food':
    			if($money < 100){
    				$sender->sendMessage(self::Message."\n"."お金がたりません...");
    			}else{
    				$item = Item::get(364, 0, 10);
    				$player->getInventory()->addItem($item);
    				$this->CoSSeMoney->addMoney($user, -100);
    				$sender->sendMessage(self::Message."\n"."調理したステーキを10個配布しました。");
    			}
    			break;

    		case 'world':
                if($level == $sigen){
                    $sender->teleport(new Position($lx, $ly, $lz, $this->getServer()->getLevelByName($live)));
                    $sender->sendMessage(self::Message."\n"."生活ワールドにワープしました！");
                }elseif($level == $live){
                    //if(!$server->isLevelLoaded($sigen)) return $this->getServer()->loadLevel($sigen) and $sender->sendMessage("[§aCoSSe§f]"."\n"."もう一度コマンドを実行してください。");
                    $sender->teleport(new Position($sx, $sy, $sz, $this->getServer()->getLevelByName($sigen)));
                    $sender->sendMessage(self::Message."\n"."資源ワールドにワープしました！");
                }
                break;

            case 'sethome':
                $this->BTH->set($user, array($x, $y, $z, $level));
                $this->BTH->save();
                $sender->sendMessage(self::Message."\n"."あなたのテレポート先を登録しました。 /homeで戻れます。");
                break;

            case 'home':
                if($this->BTH->exists($user)){
                    $x = $this->BTH->get($user)[0];
                    $y = $this->BTH->get($user)[1];
                    $z = $this->BTH->get($user)[2];
                    $level = $this->BTH->get($user)[3];
                    $pos = new Position($x, $y, $z, $this->getServer()->getLevelByName($level));
                    $sender->teleport($pos);
                    $sender->sendMessage(self::Message."\n"."設定された座標にテレポートしました。");
                }else{
                    $sender->sendMessage(self::Message."\n"."あなたのテレポート先の座標が設定されていません。 /sethomeで設定してください。");
                }
                break;

            case 'delhome':
                if($this->BTH->exists($user)){
                    $this->BTH->remove($user);
                    $this->BTH->save();
                    $sender->sendMessage(self::Message."\n"."あなたのテレポート先の座標を削除しました。");
                }else{
                    $sender->sendMessage(self::Message."\n"."あなたのテレポート先は設定されていません。 /sethomeで設定してください。");
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
        $live = $this->live;
        $acoount = $this->BCS->get($user);
        if($event->getBlock()->y <= 50 and $level == $live){
            if(!$this->SETTING->exists($user)){
                $event->setCancelled();
                $player->sendMessage(self::Message."\n"."§bY50以下のため採掘ができません。詳細は「/info 1」");
            }
        }else{
            switch ($event->getBlock()->getId()) {
                case"1":
                    $this->BCS->add($user, +2);
                    break;
                
                case"2":
                    $this->BCS->add($user, +2);
                    break;
                
                case"3":
                    $this->BCS->add($user, +2);
                    break;
                
                case"4":
                    $this->BCS->add($user, +10);
                    break;
                
                case"5":
                    $this->BCS->add($user, +10);
                    break;
                
                case"12":
                    $this->BCS->add($user, +2);
                    break;
                
                case"13":
                    $this->BCS->add($user, +2);
                    break;
                
                case"14":
                    $this->BCS->add($user, +30);
                    break;
                
                case"15":
                    $this->BCS->add($user, +30);
                    break;
                
                case"16":
                    $this->BCS->add($user, +20);
                    break;
                
                case"17":
                    $this->BCS->add($user, +20);
                    break;
                
                case"18":
                    $this->BCS->add($user, +2);
                    break;
                
                case"21":
                    $this->BCS->add($user, +25);
                    break;
                
                case"24":
                    $this->BCS->add($user, +10);
                    break;
                
                case"56":
                    $this->BCS->add($user, +100);
                    break;
                
                case"59":
                    $this->BCS->add($user, +5);
                    break;
                
                case"73":      
                case"74":
                    $this->BCS->add($user, +25);
                    break;
                
                case"80":
                    $this->BCS->add($user, +10);
                    break;
                
                case"82":
                    $this->BCS->add($user, +2);
                    break;
                
                case"86":
                    $this->BCS->add($user, +5);
                    break;
                
                case"87":
                    $this->BCS->add($user, +2);
                    break;
                
                case"88":
                    $this->BCS->add($user, +2);
                    break;
                
                case"89":
                    $this->BCS->add(user, +25);
                    break;
                
                case"103":
                    $this->BCS->add($user, +2);
                    break;
            }
        }
    }

    function onPlayerGameModeChange(PlayerGameModeChangeEvent $event){
        $player = $event->getPlayer();
        $user = $player->getName();
        if($player->isOP()){
                $player->sendMessage(self::Message."\n"."§bあなたがOPであることが確認されました。");
            }else{
                $this->getServer()->broadcastMessage("[§cCoSSe: 警告§f]"."\n".$user."§bがゲームモードを変更しようとしたのでKickしました。");
                $event->setCancelled();
                $player->kick("OPでないのにゲームモードを変更しようとしたためkickしました。", false);
            }
    }
}
