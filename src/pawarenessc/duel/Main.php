<?php

namespace pawarenessc\RFM;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\Server;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\level\Level;
use pocketmine\level\Position;

use pocketmine\math\Vector3;

use pocketmine\item\ItemIds;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use metowa1227\moneysystem\api\core\API;

class Main extends pluginBase implements Listener{
	
	public $type = [],$status = false,$stat = 0,
				 $n1 = "", $n2 = "",
				 $game = 300;
	
	public function onEnable(){
		$this->getScheduler()->scheduleRepeatingTask(new CallbackTask([$this, "gametsk"]), 20);
		$this->getLogger()->info("=========================");
		$this->getLogger()->info("Duelを読み込みました");
		$this->getLogger()->info("製作者: PawarenessC");
		$this->getLogger()->info("$this->getDescription()->getVersion()");
		$this->getLogger()->info("=========================");
		
		
		$this->config = new Config($this->getDataFolder() . "config.yml", Config::YAML,
		[
			"報酬"=>1000,
			"装備"=> array(
				"頭"=>298,
				"胴"=>299,
				"ズボン"=>300,
				"ブーツ"=>301,
			),
			"アイテム"=> array(
				"アイテム1"=>"0:0:0"
				"アイテム2"=>"0:0:0"
				"アイテム3"=>"0:0:0"
				"アイテム4"=>"0:0:0"
				"アイテム5"=>"0:0:0"
			),
		]);
		$this->invv = new Config($this->getDataFolder() . "inv.yml", Config::YAML);
		$this->xyz = new Config($this->getDataFolder() . "xyz.yml", Config::YAML, array(
			"pos1"=> array(
				"x"=>123,
				"y"=>5,
				"z"=>456,
			),
			"pos2"=> array(
				"x"=>123,
				"y"=>5,
				"z"=>456,
			),
			"world"=>"world",
			));
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$name = $event->getPlayer()->getName();
		$this->type[$name] = 3;
	}
			
	public function onQuit(PlayerQuitEvent $event){
		$name = $event->getPlayer()->getName();
		if($name == $this->n1 or $name == $this->n2){
			$p1 = $this->getServer()->getPlayer($this->n1);
			$p2 = $this->getServer()->getPlayer($this->n2);
			$this->ForceEnd();
			@$p1->teleport($level->getSafeSpawn());
			@$p2->teleport($level->getSafeSpawn());
		}
	}
			
	public function onCommand(CommandSender $sender, Command $command, string $label, array $args) : bool{
		$name = $sender->getName();
		$pre  = "§l§b1vs1 §f>>§r ";
		if($label === "pvp"){
			switch($args[0]){
				case "":
					$sender->sendMessage("{$pre}/pvp join で1vs1に参加できます");
					return true;
					break;
					
				case "help":
					$sender->sendMessage("/pvp <join | help | pos1 | pos2 | end>");
					return true;
					break;
				
				case "join":
					$this->join($sender);
					return true;
					break;
				
				case "pos1":
					$x = floor($player->x);
					$y = floor($player->y);
					$z = floor($player->z);
					$world = $player->getLevel()->getName();
					
					$data = $this->xyz->get("pos1");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					$this->xyz->set("world",$world);
					$sender->sendMessage($pre);
					$sender->sendMessage("X:{$x}");
					$sender->sendMessage("Y:{$y}");
					$sender->sendMessage("Z:{$z}");
					$sender->sendMessage("WORLD:{$world}");
					$sender->sendMessage("1つ目のスポーン地点を登録しました！");
					return true;
					break;
					
				case "pos2":
					$x = floor($player->x);
					$y = floor($player->y);
					$z = floor($player->z);
					$world = $player->getLevel()->getName();
					
					$data = $this->xyz->get("pos2");
					$data["x"] = $x;
					$data["y"] = $y;
					$data["z"] = $z;
					$this->xyz->set("world",$world);
					$sender->sendMessage($pre);
					$sender->sendMessage("X:{$x}");
					$sender->sendMessage("Y:{$y}");
					$sender->sendMessage("Z:{$z}");
					$sender->sendMessage("WORLD:{$world}");
					$sender->sendMessage("2つ目のスポーン地点を登録しました！");
					return true;
					break;
					
				case "end":
					
			}
		return true;
		}
	}
	
	public function onDeath(PlayerDeathEvent $event){
		$player = $event->getPlayer();
		$name = $player->getName();
		if($name == $this->n1){ $this->end($n1); }
		if($name == $this->n2){ $this->end($n2); }
	}
		
			
	
	public function Join($player){	
		$name = $player->getName();
		$pre  = "§l§b1vs1 §f>>§r ";
		
		$level = Server::getInstance()->getLevelByName($this->xyz->get("world"));
		$data1 = $this->xyz->get("pos1");
		$data2 = $this->xyz->get("pos2");
		$pos1 = new Position($data1["x"],$data1["y"],$data1["z"],$level);
		$pos2 = new Position($data2["x"],$data2["y"],$data2["z"],$level);
		
		if(!$this->status){
			$player->sendMessage("{$pre}§c現在ゲーム中です");
			return;
		}else{
			if($this->stat == 0){
				$player->sendMessage("{$pre}§e{$name}§cさんが1vs1に参加しました §f§rl1§r/§l2");
				$player->teleport($pos1);
				$this->setItems($player);
				$this->n1 = $name;
				$this->stat = 1;
				$player->setImmobile(true);
			}elseif($this->stat == 1){
				$this->n2 = $name;
				$this->setItems($player);
				$player->teleport($pos2);
				$player->setImmobile(true);
				$player->sendMessage("{$pre}§e{$name}§cさんが1vs1に参加しました §f§rl2§r/§l2");
				$player->sendMessage("{$pre}これより、§e{$this->n1}§rvs§e{$this->n2}§cのPvPが始まります");
				$this->status = true;
				$this->stat = 2;
			}else{
				$player->sendMessage("{$pre}§c満員です！");
				return;
			}
		}
				
	public function end($na){
		$pre  = "§l§b1vs1 §f>>§r ";
		$p1 = $this->getServer()->getPlayer($this->n1);
		$p2 = $this->getServer()->getPlayer($this->n2);
		$level = $this->getServer()->getDefaultLevel();
		$p1->teleport($level->getSafeSpawn());
		$p2->teleport($level->getSafeSpawn());
		$p1->getInventory->setContents($this->invv->get($name1));
		$p2->getInventory->setContents($this->invv->get($name2));
		if($na == $this->n1){
			$this->getServer()->broadcastMessage("{$pre}{$na}§6が勝利しました！");
			API::getInstance()->increase($p1, $this->config->get("報酬"), "1vs1", "勝利");
			$inv = $p1->getInventory();
			$i = $inv->getItemInHand();
			$inv->setItemInHand(Item::get(ItemIds::TOTEM));
			$p->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$inv->setItemInHand($i);/*冬月さんありがとうございました！*/
			
			$p2->sendMessage("{$pre}§c敗北しました...");
		}else{
			$this->getServer()->broadcastMessage("{$pre}{$na}§6が勝利しました！");
			API::getInstance()->increase($p2, $this->config->get("報酬"), "1vs1", "勝利");
			$inv = $p2->getInventory();
			$i = $inv->getItemInHand();
			$inv->setItemInHand(Item::get(ItemIds::TOTEM));
			$p->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$inv->setItemInHand($i);/*冬月さんありがとうございました！*/
			
			$p1->sendMessage("{$pre}§c敗北しました...");
		}
		$this->stat = 0;
		$this->status  = false;
		$this->n1 = "";
		$this->n2 = "";
	}
	
	public function ForceEnd($bool=false){
		$pre  = "§l§b1vs1 §f>>§r ";
		$level = $this->getServer()->getDefaultLevel();
		$p1->teleport($level->getSafeSpawn());
		$p2->teleport($level->getSafeSpawn())
		if($bool){
			$this->getServer()->broadcastMessage("{$pre}PvPの退出があったので強制終了しました");
			$this->stat = 0;
			$this->status  = false;
			$name1 = $p1->getName();
			$name2 = $p2->getName();
			$p1->getInventory->setContents($this->invv->get($name1));
			$p2->getInventory->setContents($this->invv->get($name2));
			$this->n1 = "";
			$this->n2 = "";
		}else{
			$this->getServer()->broadcastMessage("{$pre}PvPが権限者によって強制終了しました");
			$this->stat = 0;
			$this->status  = false;
			$name1 = $p1->getName();
			$name2 = $p2->getName();
			$p1->getInventory->setContents($this->invv->get($name1));
			$p2->getInventory->setContents($this->invv->get($name2));
			$this->n1 = "";
			$this->n2 = "";
		}
	}
	
	public function oto($player,$s="pop"){	
		switch($s){
			case "pop":
			$pk = new PlaySoundPacket;
			$pk->soundName = "random.pop";
			$pk->x = $player->x;
			$pk->y = $player->y;
			$pk->z = $player->z;
			$pk->volume = 1;
			$pk->pitch = 1;
			$player->sendDataPacket($pk);
			break;
			
			case "anvil":
 			$pk = new PlaySoundPacket;
			$pk->soundName = "random.anvil_land";
			$pk->x = $player->x;
			$pk->y = $player->y;
			$pk->z = $player->z;
			$pk->volume = 0.5;
			$pk->pitch = 1;
			$player->sendDataPacket($pk);
 			break;
		}
	}
		
	public function gametsk(){
		if($this->status){
			$p1 = $this->getServer()->getPlayer($this->n1);
			$p2 = $this->getServer()->getPlayer($this->n2);
			$this->game--;
			switch($this->game){
				case 305:
				$this->oto($p1,"pop");
				$this->oto($p2,"pop");
				$p1->addTitle(">> 5 <<");
				$p2->addTitle(">> 5 <<");
				break;
				
				case 304:
				$this->oto($p1,"pop");
				$this->oto($p2,"pop");
				$p1->addTitle(">> 4 <<");
				$p2->addTitle(">> 4 <<");
				break;
					
				case 303:
				$this->oto($p1,"pop");
				$this->oto($p2,"pop");
				$p1->addTitle(">> 3 <<");
				$p2->addTitle(">> 3 <<");
				break;
					
				case 302:
				$this->oto($p1,"pop");
				$this->oto($p2,"pop");
				$p1->addTitle(">> 2 <<");
				$p2->addTitle(">> 2 <<");
				break;
					
				case 301:
				$this->oto($p1,"pop");
				$this->oto($p2,"pop");
				$p1->addTitle(">> 1 <<");
				$p2->addTitle(">> 1 <<");
				break;
				
				case 300:
				$this->oto($p1,"anvil");
				$this->oto($p2,"anvil");
				$p1->addTitle("§4START!!");
				$p2->addTitle("§4START!!");
				$p1->setImmobile(true);
				$p2->setImmobile(true);
				break;
				
				if($this->game < 300){ $player->sendPopup("§c{$this->game}"); }
				
				case 0:
				$this->draw();
				break;
			}
		}
	}
		
	public function draw(){
		$pre  = "§l§b1vs1 §f>>§r ";
		$p1 = $this->getServer()->getPlayer($this->n1);
		$p2 = $this->getServer()->getPlayer($this->n2);
		$name1 = $p1->getName();
		$name2 = $p2->getName();
		$p1->getInventory->setContents($this->invv->get($name1));
		$p2->getInventory->setContents($this->invv->get($name2));
		$level = $this->getServer()->getDefaultLevel();
		$p1->teleport($level->getSafeSpawn());
		$p2->teleport($level->getSafeSpawn())
		
		$this->getServer()->broadcastMessage("{$pre}PvPは引き受けでした...");
		$this->stat = 0;
		$this->status  = false;
		$this->n1 = "";
		$this->n2 = "";
	}
	
	public function setItems($player){
		$name = $player->getName();
		$inv = $player->getInventory->getContents();
		$this->invv->set($name,$inv);
		$this->innvv->save();
		$player->getInventory()->clearAll();
		
		$data = $this->config->get("防具");
		$armor = $player->getArmorInventory();
		$armor->setHelmet(Item::get($data["ヘルメット"],0,1));
		$armor->setChestplate(Item::get($data["装備"]["チェストプレート"],0,1));
		$armor->setLeggings(Item::get($data["装備"]["レギンス"],0,1));
		$armor->setBoots(Item::get($data["装備"]["ブーツ"],0,1));
		
		foreach($this->config->get("アイテム") as $item){
			$item = explode(":",$item);
			$item = Item::get($item[0],$item[1],$item[2]);
			$player->getInventory()->addItem($item);
		)
	)
		
}
				
					
			
		
class CallbackTask extends Task{

	public function __construct(callable $callable, array $args = []){
		$this->callable = $callable;
		$this->args = $args;

	}
	public function onRun($tick){
		call_user_func_array($this->callable, $this->args);
	}
}
