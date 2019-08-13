<?php

namespace pawarenessc\RFM;

use pocketmine\utils\Config;
use pocketmine\utils\TextFormat;

use pocketmine\event\Listener;
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
				 $n1 = "", $n2 = "";
	
	public function onEnable(){
		$this->getLogger()->info("=========================");
		$this->getLogger()->info("Duelを読み込みました");
		$this->getLogger()->info("製作者: PawarenessC");
		$this->getLogger()->info("$this->getDescription()->getVersion()");
		$this->getLogger()->info("=========================");
		
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
	}
	
	public function onJoin(PlayerJoinEvent $event){
		$name = $event->getPlayer()->getName();
		$this->type[$name] = 3;
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
					$sender->sendMessage("/pvp <join | help | pos1 | pos2 | setup>");
				
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
				$this->n1 = $name;
				$this->stat = 1;
			}elseif($this->stat == 1){
				$this->n2 = $name;
				$player->sendMessage("{$pre}§e{$name}§cさんが1vs1に参加しました §f§rl2§r/§l2");
				$player->sendMessage("{$pre}これより、§e{$this->n1}§rvs§e{$this->n2}§cのPvPが始まります");
				$this->status = true;
				$this->stat = 2;
			}else{
				$player->sendMessage("{$pre}§e{$name}§c満員です！");
				return;
			}
		}
				
	public function end($na){
		$pre  = "§l§b1vs1 §f>>§r ";
		$p1 = $this->getServer()->getPlayer($this->n1);
		$p2 = $this->getServer()->getPlayer($this->n2);
		if($na == $this->n1){
			$this->getServer()->broadcastMessage("{$pre}$na§6が勝利しました！");
			API::getInstance()->increase($p1, $this->config->get("報酬"), "1vs1", "勝利");
			$inv = $p1->getInventory();
			$i = $inv->getItemInHand();
			$inv->setItemInHand(Item::get(ItemIds::TOTEM));
			$p->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$inv->setItemInHand($i);/*冬月さんありがとうございました！*/
			
			$p2->sendMessage("{$pre}§c敗北しました...");
		}else{
			$this->getServer()->broadcastMessage("{$pre}$na§6が勝利しました！");
			API::getInstance()->increase($p2, $this->config->get("報酬"), "1vs1", "勝利");
			$inv = $p2->getInventory();
			$i = $inv->getItemInHand();
			$inv->setItemInHand(Item::get(ItemIds::TOTEM));
			$p->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
			$inv->setItemInHand($i);/*冬月さんありがとうございました！*/
			
			$p1->sendMessage("{$pre}§c敗北しました...");
		}
	}
