<?php
/**
* author: advocaite aka serverkart_rod
* MONETISE YOUR POCKETMINE SERVER WITH http://serverkart.com
* skype: advocaite
*/

namespace essentialsTP;

use pocketmine\command\Command;
use pocketmine\command\CommandExecutor;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\Server;
use pocketmine\event\player\PlayerDeathEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerRespawnEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerBedEnterEvent;
use pocketmine\event\Listener;
use pocketmine\level\Position;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Player;
use pocketmine\permission\Permissible;
use pocketmine\IPlayer;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\utils\Config;
use pocketmine\tile\Sign;
use pocketmine\event\block\SignChangeEvent;





class essentialsTP extends PluginBase  implements CommandExecutor, Listener {
  private $db2;
  public $username;
  public $world;
  public $home_loc;
  public $warp_loc;
  public $params;
  public $death_loc;
  public $config;
  public $player_cords;
  public $tp_sender;
  public $tp_reciver;
  public $result;
  public $prepare;
  public $sign;

  public function fetchall(){
    $row = array();

    $i = 0;

    while($res = $this->result->fetchArray(SQLITE3_ASSOC)){

      $row[$i] = $res;
      $i++;

    }
    return $row;
  }

  public function onLoad(){

  }

  public function onCommand(CommandSender $sender, Command $cmd, $label, array $args){
    switch($cmd->getName()){
      case 'tpa':
      if (!$sender->hasPermission("rank.lapis")) {
        $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
        return true;
      }
      if ($sender instanceof Player)
      {
        if ((count($args) != 0) && (count($args) < 2)) {
          if (trim(strtolower($sender->getName())) == trim(strtolower($args[0]))) {
            $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_teleport_self"));
            return true;
          }
          $this->tp_sender  = $sender->getName();
          $this->tp_reciver = this->getServer()->getPlayer($args[0])->getName();
          if ($this->getServer()->getPlayer($this->tp_reciver) instanceof Player) {
            $sender->sendMessage("§6Your tpa request has been sent");
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage(TextFormat::GOLD . $this->tp_sender . TextFormat::WHITE . ' '.$this->config->get("Lang_sent_request_you"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' ' . TextFormat::GOLD . '/tpaccept' . TextFormat::WHITE . ' '.$this->config->get("Lang_accept_request"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' ' . TextFormat::GOLD . '/tpdecline' . TextFormat::WHITE . ' '.$this->config->get("Lang_decline_request"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_request_expire_1").' ' . TextFormat::GOLD .$this->config->get("tpa-here-cooldown").' '.$this->config->get("Lang_request_expire_2") . TextFormat::WHITE . ' '.$this->config->get("Lang_request_expire_3"));
            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
            $this->prepare->bindValue(":name", trim(strtolower($this->tp_reciver)), SQLITE3_TEXT);
            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
            $this->prepare->bindValue(":type", 'tpa', SQLITE3_TEXT);
            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
            $this->result = $this->prepare->execute();
            return true;
          } else {
            $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_player_not_online"));
            return true;
          }
        } else {
          $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_invalid_usage"));
          return false;
        }

      }
      else
      {
        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
        return true;
      }
      break;
      case 'tpahere':
      if (!$sender->hasPermission("rank.lapis")) {
        $sender->sendMessage(TextFormat::RED . $this->config->get("Lang_no_permissions"));
        return true;
      }
      if ($sender instanceof Player)
      {
        if((count($args) != 0) && (count($args) < 2))
        {
          if(trim(strtolower($sender->getName())) == trim(strtolower($args[0]))){$sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_teleport_self"));return true;}
          $this->tp_sender = $sender->getName();
          $this->tp_reciver = this->getServer()->getPlayer($args[0])->getName();
          if($this->getServer()->getPlayer($this->tp_reciver) instanceof Player)
          {
            $sender->sendMessage("§6Your tpa request has been sent");
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage(TextFormat::GOLD.$this->tp_sender.TextFormat::WHITE.' '.$this->config->get("Lang_sent_request_them"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' '.TextFormat::GOLD.'/tpaccept'.TextFormat::WHITE.' '.$this->config->get("Lang_accept_request"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_type").' '.TextFormat::GOLD.'/tpdecline'.TextFormat::WHITE.' '.$this->config->get("Lang_decline_request"));
            $this->getServer()->getPlayer($this->tp_reciver)->sendMessage($this->config->get("Lang_request_expire_1").' '.TextFormat::GOLD.$this->config->get("tpa-here-cooldown").' '.$this->config->get("Lang_request_expire_2").TextFormat::WHITE.' '.$this->config->get("Lang_request_expire_3"));
            $this->prepare = $this->db2->prepare("INSERT INTO tp_requests (player, player_from, type, time, status) VALUES (:name, :name_from, :type, :time, :status)");
            $this->prepare->bindValue(":name", trim(strtolower($this->tp_reciver)), SQLITE3_TEXT);
            $this->prepare->bindValue(":name_from", trim(strtolower($this->tp_sender)), SQLITE3_TEXT);
            $this->prepare->bindValue(":type", 'tpahere', SQLITE3_TEXT);
            $this->prepare->bindValue(":time", time(), SQLITE3_TEXT);
            $this->prepare->bindValue(":status", 0, SQLITE3_TEXT);
            $this->result = $this->prepare->execute();
            return true;
          }
          else
          {
            $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
            return true;
          }
        }
        else
        {
          $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_invalid_usage"));
          return false;
        }
      }
      else
      {
        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
        return true;
      }
      break;
      case 'tpaccept':
      if ($sender instanceof Player)
      {
        $this->prepare = $this->db2->prepare("SELECT id,player, player_from, type, time, status FROM tp_requests WHERE time > :time AND player = :player AND status = 0");
        $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
        $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
        $this->result = $this->prepare->execute();
        $sql          = $this->fetchall();
        if(count($sql) > 0)
        {
          $sql = $sql[0];
          switch($sql['type'])
          {
            case 'tpa':
            if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
            {
              $this->getServer()->getPlayer($sql['player_from'])->teleport($sender->getPosition());
              $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
              $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
              $this->result = $this->prepare->execute();
              return true;
            }
            else
            {
              $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
              return true;
            }
            break;
            case 'tpahere':
            if($this->getServer()->getPlayer($sql['player_from']) instanceof Player)
            {
              $sender->teleport($this->getServer()->getPlayer($sql['player_from'])->getPosition());
              $this->prepare = $this->db2->prepare("UPDATE tp_requests SET status = 1 WHERE id = :id");
              $this->prepare->bindValue(":id", $sql['id'], SQLITE3_INTEGER);
              $this->result = $this->prepare->execute();
              return true;
            }
            else
            {
              $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_player_not_online"));
              return true;
            }
            break;
            default:
            return false;
          }
        }
        else
        {
          $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_active_request"));
          $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE time < :time AND player = :player AND status = 0");
          $this->prepare->bindValue(":time", (time() - $this->config->get("tpa-here-cooldown")), SQLITE3_TEXT);
          $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
          $this->result = $this->prepare->execute();
          return true;
        }
      }
      else
      {
        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
        return true;
      }
      break;
      case 'tpdeny':
      if ($sender instanceof Player)
      {
        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_no_active_request"));
        $this->prepare = $this->db2->prepare("DELETE FROM tp_requests WHERE player = :player AND status = 0");
        $this->prepare->bindValue(":player", trim(strtolower($sender->getName())), SQLITE3_TEXT);
        $this->result = $this->prepare->execute();
        return true;
      }
      else
      {
        $sender->sendMessage(TextFormat::RED.$this->config->get("Lang_command_only_use_ingame"));
        return true;
      }
      break;
      default:
      return false;
    }
  }

  public function create_db(){
    $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='homes'");
    $this->result = $this->prepare->execute();
    $sql = $this->fetchall();
    $count = count($sql);
    if ($count == 0){
      $this->prepare = $this->db2->prepare("CREATE TABLE homes (
        id INTEGER PRIMARY KEY,
        player TEXT,
        x TEXT,
        y TEXT,
        z TEXT,
        title TEXT,
        world TEXT)");
        $this->result = $this->prepare->execute();
        $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ Homes database created!");
      }
      $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='tp_requests'");
      $this->result = $this->prepare->execute();
      $sql2 = $this->fetchall();
      $count2 = count($sql2);
      if ($count2 == 0){
        $this->prepare = $this->db2->prepare("CREATE TABLE tp_requests (
          id INTEGER PRIMARY KEY,
          player TEXT,
          player_from TEXT,
          type TEXT,
          time TEXT,
          status TEXT)");
          $this->result = $this->prepare->execute();
          $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ request database created!");
        }
        $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='warps'");
        $this->result = $this->prepare->execute();
        $sql3 = $this->fetchall();
        $count3 = count($sql3);
        if($count3 == 0){
          $this->prepare = $this->db2->prepare("CREATE TABLE warps (
            id INTEGER PRIMARY KEY,
            x TEXT,
            y TEXT,
            z TEXT,
            world TEXT,
            title TEXT)");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ warps database created!");
          }
          $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='spawns'");
          $this->result = $this->prepare->execute();
          $sql4 = $this->fetchall();
          $count4 = count($sql4);
          if($count4 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE spawns (
              id INTEGER PRIMARY KEY,
              x TEXT,
              y TEXT,
              z TEXT,
              world TEXT
            )");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ Spawns database created!");
          }
          $this->prepare = $this->db2->prepare("SELECT * FROM sqlite_master WHERE type='table' AND name='cooldowns'");
          $this->result = $this->prepare->execute();
          $sql5 = $this->fetchall();
          $count5 = count($sql5);
          if($count5 == 0){
            $this->prepare = $this->db2->prepare("CREATE TABLE cooldowns (
              id INTEGER PRIMARY KEY,
              home INTEGER,
              warp INTEGER,
              spawn INTEGER,
              player TEXT
            )");
            $this->result = $this->prepare->execute();
            $this->getLogger()->info(TextFormat::AQUA."essentialsTP+ cooldown database created!");
          }

        }

        public function check_config(){
          $this->saveDefaultConfig();
          $this->config = new Config($this->getDataFolder()."config.yml", Config::YAML, array());
          $this->config->set('plugin-name',"essentalsTp+");
          $this->config->save();

          if(!$this->config->get("sqlite-dbname"))
          {
            $this->config->set("sqlite-dbname", "essentials_tp");
            $this->config->save();
          }

          if($this->config->get("tpa-here-cooldown") == false)
          {
            $this->config->set("tpa-here-cooldown", "30");
            $this->config->save();
          }
        }

        public function onEnable(){
          $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loading...");
          @mkdir($this->getDataFolder());
          $this->check_config();
          try{
            if(!file_exists($this->getDataFolder().$this->config->get("sqlite-dbname").'.db')){
              $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE | SQLITE3_OPEN_CREATE);
            }else{
              $this->db2 = new \SQLite3($this->getDataFolder().$this->config->get("sqlite-dbname").'.db', SQLITE3_OPEN_READWRITE);
            }
          }
          catch (Exception $e)
          {
            echo $e->getMessage();
            die();
          }
          $this->create_db();
          $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."]....");
          $this->tpa_cooldown = time() - $this->config->get("tpa-here-cooldown");
          $this->getLogger()->info(TextFormat::GREEN."[INFO] loading [".TextFormat::GOLD."config.yml".TextFormat::GREEN."] DONE");
          $this->getLogger()->info(TextFormat::GREEN."essentialsTP+ loaded!");
          $this->getServer()->getPluginManager()->registerEvents($this, $this);
        }

        public function onDisable(){
          $this->prepare->close();
          $this->getLogger()->info("essentialsTP+ Disabled");
        }
      }
