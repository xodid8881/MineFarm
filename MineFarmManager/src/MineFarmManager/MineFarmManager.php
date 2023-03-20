<?php
declare(strict_types=1);

namespace MineFarmManager;

use pocketmine\player\Player;
use pocketmine\Server;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\Config;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;

use MineFarmManager\Commands\FarmCommand;
use MineFarmManager\Commands\FarmSettingCommand;
use MineFarmManager\Commands\FarmSetCommand;
use MineFarmManager\Commands\MyFarmCommand;

use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;

use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldManager;

use LifeInventoryLib\LifeInventoryLib;
use LifeInventoryLib\InventoryLib\LibInvType;

use pocketmine\scheduler\Task;
use pocketmine\scheduler\AsyncTask;

use MoneyManager\MoneyManager;

use ConnectTimes\ConnectTimes;

class MineFarmManager extends PluginBase {

  protected $config;
  public $db;
  public $get = [];
  private static $instance = null;

  public static function getInstance(): MineFarmManager
  {
    return static::$instance;
  }

  public function onLoad():void
  {
    self::$instance = $this;
  }

  public function onEnable():void
  {
    $this->player = new Config ($this->getDataFolder() . "players.yml", Config::YAML);
    $this->pldb = $this->player->getAll();
    $this->level = new Config ($this->getDataFolder() . "levels.yml", Config::YAML);
    $this->leveldb = $this->level->getAll();
    $this->levelmy = new Config ($this->getDataFolder() . "levelmoneys.yml", Config::YAML);
    $this->levelmydb = $this->levelmy->getAll();
    $this->rank = new Config ($this->getDataFolder() . "ranks.yml", Config::YAML,
    [
      "씨앗" => 0,
      "새싹" => 10800,
      "풀잎" => 108000,
      "나무" => 324000,
      "숲속" => 648000,
      "초원" => 1296000
    ]);
    $this->rankdb = $this->rank->getAll();
    $this->ranking = new Config ($this->getDataFolder() . "rankings.yml", Config::YAML,
    [
      "랭킹" => []
    ]);
    $this->rankingdb = $this->ranking->getAll();
    $this->world = new Config ($this->getDataFolder() . "worlds.yml", Config::YAML,
    [
      "섬번호" => 0
    ]);
    $this->worlddb = $this->world->getAll();
    $this->worldjoin = new Config ($this->getDataFolder() . "worldjoins.yml", Config::YAML);
    $this->worldjoindb = $this->worldjoin->getAll();
    $this->shoplist = new Config ($this->getDataFolder() . "shoplists.yml", Config::YAML);
    $this->shoplistdb = $this->shoplist->getAll();
    $this->getServer()->getCommandMap()->register('MineFarmManager', new FarmCommand($this));
    $this->getServer()->getCommandMap()->register('MineFarmManager', new FarmSettingCommand($this));
    $this->getServer()->getCommandMap()->register('MineFarmManager', new FarmSetCommand($this));
    $this->getServer()->getCommandMap()->register('MineFarmManager', new MyFarmCommand($this));
    $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    $this->getScheduler()->scheduleRepeatingTask(new UnloadTask($this), 20 * 60);
  }

  public function tag() : string
  {
    return "§l§b[팜]§r§7 ";
  }

  public function getFarmShareLists($player) : array{
    $arr = [];
    $number = $this->pldb [strtolower($player->getName ())] ["섬번호"];
    foreach($this->worldjoindb ["{$number}"] ["공유목록"] as $name => $v){
      array_push($arr, $name);
    }
    return $arr;
  }

  public function getFarmRankShareLists($name) : array{
    $arr = [];
    $number = $this->pldb [strtolower($name)] ["섬번호"];
    foreach($this->worldjoindb ["{$number}"] ["공유목록"] as $name => $v){
      array_push($arr, $name);
    }
    return $arr;
  }

  public function getFarmblockLists($player) : array{
    $arr = [];
    $number = $this->pldb [strtolower($player->getName ())] ["섬번호"];
    foreach($this->worldjoindb ["{$number}"] ["차단정보"] as $name => $v){
      array_push($arr, $name);
    }
    return $arr;
  }

  public function CheckFarmXZ($event,$number) {
    $player = $event->getPlayer();
    $name = $player->getName ();
    $block = $event->getBlock ();
    $pos = $block->getPosition();
    if($this->worldjoindb ["{$number}"] ["팜등급"] == "평원"){
      if($pos->x > 80 or $pos->x < -80 or $pos->z > 80 or $pos->z < -80){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 80X80 입니다.');
        $event->cancel ();
        return "false";
      }
    }

    if($this->worldjoindb ["{$number}"] ["팜등급"] == "숲속"){
      if($pos->x > 60 or $pos->x < -60 or $pos->z > 60 or $pos->z < -60){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 60X60 입니다.');
        $event->cancel ();
        return "false";
      }
    }
    if($this->worldjoindb ["{$number}"] ["팜등급"] == "나무"){
      if($pos->x > 55 or $pos->x < -55 or $pos->z > 55 or $pos->z < -55){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 55X55 입니다.');
        $event->cancel ();
        return "false";
      }
    }
    if($this->worldjoindb ["{$number}"] ["팜등급"] == "풀잎"){
      if($pos->x > 50 or $pos->x < -50 or $pos->z > 50 or $pos->z < -50){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 50X50 입니다.');
        $event->cancel ();
        return "false";
      }
    }
    if($this->worldjoindb ["{$number}"] ["팜등급"] == "새싹"){
      if($pos->x > 45 or $pos->x < -45 or $pos->z > 45 or $pos->z < -45){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 45X45 입니다.');
        $event->cancel ();
        return "false";
      }
    }
    if($this->worldjoindb ["{$number}"] ["팜등급"] == "씨앗"){
      if($pos->x > 40 or $pos->x < -40 or $pos->z > 40 or $pos->z < -40){
        $player->sendTip( $this->tag() . '당신의 팜의 최대크기는 40X40 입니다.');
        $event->cancel ();
        return "false";
      }
    }
  }

  public function CheckFarmY($event,$number) {
    $player = $event->getPlayer();
    $name = $player->getName ();
    $block = $event->getBlock ();
    $pos = $block->getPosition();
    if($pos->y > 80){
      $player->sendTip( $this->tag() . '팜의 최대설치 높이는 80 입니다.');
      $event->cancel ();
      return "false";
    }
  }

  public function LevelSeeGUI($player,$number) {
  
    $name = $player->getName ();
    $level = $this->worldjoindb [$number] ["레벨"];
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("HOPPER", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), "[ 팜 ] | 해당 플레이어 팜정보",$player);
    $UPOneBlock = $this->worldjoindb [$number] ["레벨블럭"] ["1레벨배너"];
    $UPTwoBlock = $this->worldjoindb [$number] ["레벨블럭"] ["3레벨배너"];
    $UPThreeBlock = $this->worldjoindb [$number] ["레벨블럭"] ["5레벨배너"];
    $UPFourBlock = $this->worldjoindb [$number] ["레벨블럭"] ["10레벨배너"];
    $UPFiveBlock = $this->worldjoindb [$number] ["레벨블럭"] ["20레벨배너"];
    $inv->setItem( 0 , ItemFactory::getInstance()->get(446, 9, 1)->setCustomName("1레벨 배너")->setLore([ "팜에 총 설치한 갯수 : {$UPOneBlock}\n상승 된 총 레벨 : " . $UPOneBlock ]));
    $inv->setItem( 1 , ItemFactory::getInstance()->get(446, 12, 1)->setCustomName("3레벨 배너")->setLore([ "팜에 총 설치한 갯수 : {$UPTwoBlock}\n상승 된 총 레벨 : " . $UPTwoBlock*3 ]));
    $inv->setItem( 2 , ItemFactory::getInstance()->get(446, 14, 1)->setCustomName("5레벨 배너")->setLore([ "팜에 총 설치한 갯수 : {$UPThreeBlock}\n상승 된 총 레벨 : " . $UPThreeBlock*5 ]));
    $inv->setItem( 3 , ItemFactory::getInstance()->get(446, 6, 1)->setCustomName("10레벨 배너")->setLore([ "팜에 총 설치한 갯수 : {$UPFourBlock}\n상승 된 총 레벨 : " . $UPFourBlock*10 ]));
    $inv->setItem( 4 , ItemFactory::getInstance()->get(446, 15, 1)->setCustomName("20레벨 배너")->setLore([ "팜에 총 설치한 갯수 : {$UPFiveBlock}\n상승 된 총 레벨 : " . $UPFiveBlock*20 ]));

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function PlayerPointGUI($player):void
  {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), '[ 팜 ] | 순위',$player);
    $i = 0;
    while ($i <= 8){
      $cup = explode ( " ", "14 22 23 24 30 31 32 33 34" );
      $number = (int)$cup[$i];
      $inv->setItem( $number , ItemFactory::getInstance()->get(199, 0, 1)->setCustomName("§l§7공백"));
      ++$i;
    }
    arsort($this->rankingdb ["점수랭킹"]);
    $count = 0;
    foreach (array_keys($this->rankingdb ["점수랭킹"]) as $name) {
      if (0 <= $count and 8 >= $count) {
        if (isset($this->pldb [$name] ["섬번호"])) {
          $number = $this->pldb [$name] ["섬번호"];
          $point = (int)$this->worldjoindb ["{$number}"] ["점수"];
          $rank = $this->worldjoindb ["{$number}"] ["팜등급"];
          $Suggestion = $this->worldjoindb ["{$number}"] ["추천"];
          $Level = $this->worldjoindb ["{$number}"] ["레벨"];
          $arr = [];
          foreach($this->getFarmRankShareLists($name) as $list) {
            $arr[] = "§f•" . $list . "\n";
          }
          $randcount = $count+1;
          $cup = explode ( " ", " 14 22 23 24 30 31 32 33 34" );
          $number = (int)$cup[$randcount];
          $inv->setItem( $number , ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§l§f{$name} §b님")->setLore([ "§b§l| §f순위 §b- §f" . $randcount . " §b위\n§b§l| §f레벨 §b- §f{$Level}§b.Lv\n§b§l| §f추천수 §b- §f{$Suggestion} §b회\n§b§l| §f점수 §b- §f{$point} §b점\n§b§l| §f등급 §b- §f{$rank} 등급\n\n§b§l| §f공유자 목록 §b- §f\n" . implode("", $arr) ]));
          $count++;
        }
      }
    }
    $i = 0;
    while ($i <= 42){
      $rand = explode ( " ", "0 1 2 3 4 5 6 7 8 9 10 11 12 13 15 16 17 18 20 21 25 26 27 29 35 36 37 38 39 40 41 42 43 44 45 46 47 48 49 50 51 52 53" );
      $number = (int)$rand[$i];
      $inv->setItem( $number , ItemFactory::getInstance()->get(63, 0, 1)->setCustomName(" "));
      ++$i;
    }
    $inv->setItem( 19 , ItemFactory::getInstance()->get(384, 0, 1)->setCustomName("§l§b레벨순서")->setLore([ "팜 순위를 레벨 순으로 변경합니다." ]));
    $inv->setItem( 28 , ItemFactory::getInstance()->get(17, 0, 1)->setCustomName("§l§b점수순서")->setLore([ "팜 순위를 점수 순으로 변경합니다." ]));
    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function SeePointList($player, $date, $Rank)
  {
    if (!is_numeric ($date)){
      if (!isset($this->plugin->pldb [strtolower($date)])) {
        $player->sendMessage ($this->tag()."{$date} 플레이어는 존재하지 않습니다.");
        return true;
      }
      arsort($this->rankingdb ["{$Rank}랭킹"]);
      $count = 0;
      foreach (array_keys($this->rankingdb ["{$Rank}랭킹"]) as $name) {
        $count ++;
        if ($name == $date){
          $number = $this->pldb [$name] ["섬번호"];
          $point = (int)$this->worldjoindb ["{$number}"] ["점수"];
          $level = (int)$this->worldjoindb ["{$number}"] ["레벨"];
          $rank = $this->worldjoindb ["{$number}"] ["팜등급"];
          if ($Rank == "레벨"){
            $player->sendMessage("§l§b[{$count}위] §r§7" . $name . "님: §l§f{$level}.Lv");
            return true;
          } else if ($Rank == "점수"){
            $player->sendMessage("§l§b[{$count}위] §r§7" . $name . "님: §l§f{$point} 점 | {$rank} 등급");
            return true;
          }
        }
      }
    } else {
      arsort($this->rankingdb ["{$Rank}랭킹"]);
      $count = 0;
      foreach (array_keys($this->rankingdb ["{$Rank}랭킹"]) as $name) {
        $count ++;
        $number = $this->pldb [$name] ["섬번호"];
        if ($number == $date){
          $number = $this->pldb [$name] ["섬번호"];
          $point = (int)$this->worldjoindb ["{$number}"] ["점수"];
          $level = (int)$this->worldjoindb ["{$number}"] ["레벨"];
          $rank = $this->worldjoindb ["{$number}"] ["팜등급"];
          if ($Rank == "레벨"){
            $player->sendMessage("§l§b[{$count}위] §r§7" . $name . "님: §l§f{$level}.Lv");
            return true;
          } else if ($Rank == "점수"){
            $player->sendMessage("§l§b[{$count}위] §r§7" . $name . "님: §l§f{$point} 점 | {$rank} 등급");
            return true;
          }
        }
      }
      $player->sendMessage ($this->tag()."{$date} 번호의 팜은 존재하지 않습니다.");
      return true;
    }
  }

  public function getMaxPage(): float
  {
    $count = count($this->rankingdb ["랭킹"]);
    return floor($count / 5) + 1;
  }

  public function PlayerRank($player, $name):void
  {
    if (isset($this->pldb [strtolower($name)] ["섬번호"])) {
      if (! is_numeric ($name)) {
        $count = $this->pldb [strtolower($name)] ["섬번호"];
      } else {
        $count = $name;
      }
      foreach($this->pldb as $name => $v){
        if ($name != "목록") {
          $number = $this->pldb [$name] ["섬번호"];
          if ($count == $number) {
            $point = (int)$this->worldjoindb ["{$number}"] ["점수"];
            $rank = $this->worldjoindb ["{$number}"] ["팜등급"];
            $player->sendMessage($this->tag () . $name . "님의 팜 정보 : §l§f{$point} 점 | {$rank} 등급");
          }
        }
      }
    } else {
      $player->sendMessage($this->tag () . $name . "님의 팜은 존재하지 않아 등급 확인이 불가합니다.");
    }
  }

  public function onOpen($player):void
  {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DROPPER", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), '[ 팜 ] | 구입도우미',$player);
    $i = 0;
    foreach($this->leveldb as $name => $v){
      if ($name != "섬번호") {
        $inv->setItem( $i , ItemFactory::getInstance()->get(386, 0, 1)->setCustomName("{$name}"));
        ++$i;
      }
    }
    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function BuyTaskEvent ($player) {
    $this->getScheduler()->scheduleDelayedTask(new class ($this, $player) extends Task {
      protected $owner;
      public function __construct(MineFarmManager $owner,Player $player) {
        $this->owner = $owner;
        $this->player = $player;
      }
      public function onRun():void {
        $this->owner->BuyManagerUI($this->player);
      }
    }, 10);
  }
  public function BuyManagerUI(Player $player):void
  {
    $name = $player->getName ();
    if ($this->pldb ["목록"] [strtolower($name)] ["구매정보"] == "") return;
    $money = $this->levelmydb [$this->pldb ["목록"] [strtolower($name)] ["구매정보"]];
    $koreamoney = MoneyManager::getInstance ()->getKoreanMoney ($money);
    $encode = [
      'type' => 'form',
      'title' => '[ 마인팜 ]',
      'content' => "
      선택한 팜을 구매하겠습니까?

      {$this->pldb ["목록"] [strtolower($name)] ["구매정보"]} 를(을) 선택했습니다.
      {$this->pldb ["목록"] [strtolower($name)] ["구매정보"]} 가격은 {$koreamoney} 입니다.
      ",
      'buttons' => [
        [
          'text' => '구매하기'
        ],
        [
          'text' => '취소하기'
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 6573;
    $packet->formData = json_encode($encode);
    $player->getNetworkSession()->sendDataPacket($packet);
  }

  public function ShopOpen($player) {
    $name = $player->getName ();
    $playerpage = $this->pldb ["목록"] [strtolower($name)] ["페이지"];
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), '[ 팜 ] | 매입 도우미',$player);

    $i = 0;
    $page = 1;
    if (isset ($this->worlddb ["판매마인팜"])){
      foreach($this->worlddb ["판매마인팜"] as $FarmNumber => $v){
        if ( $i <= 48) {
          $this->shoplistdb ["리스트"] [$page] [$i] = $FarmNumber;
          $this->save ();
        } else {
          ++$page;
          $pageData = (int)$page-1;
          $getpage = (int)$page*49;
          $iData = $page-$getpage;
          $this->shoplistdb ["리스트"] [$page] [$iData] = $FarmNumber;
          $this->save ();
        }
        ++$i;
      }
    }
    $i = 0;
    if (isset ($this->worlddb ["리스트"])){
      foreach($this->shoplistdb ["리스트"] [$playerpage] as $iData => $v){
        $number = $this->shoplistdb ["리스트"] [$playerpage] [$iData];
        $money = $this->worlddb ["판매마인팜"] ["{$number}"];
        $inv->setItem( $i , ItemFactory::getInstance()->get(386, 0, 1)->setCustomName("{$number}")->setLore([ "가격 : {$money} 원" ]));
        ++$i;
      }
    }
    $inv->setItem( 49 , ItemFactory::getInstance()->get(426, 0, 1)->setCustomName("구매모드")->setLore([ "해당 아이템을 인벤토리로 옴기고 물품을 옴기면 구매창이 오픈!\n인벤토리로 가져가보세요." ]) );
    $inv->setItem( 50 , ItemFactory::getInstance()->get(166, 0, 1)->setCustomName("이동모드")->setLore([ "해당 아이템을 인벤토리로 옴기고 물품을 옴기면 해당 팜으로 이동!\n인벤토리로 가져가보세요." ]) );
    $inv->setItem( 51 , ItemFactory::getInstance()->get(368, 0, 1)->setCustomName("이전페이지")->setLore([ "해당 아이템을 인벤토리로 옴기면 이전페이지로 이동!.\n인벤토리로 가져가보세요." ]) );
    $inv->setItem( 52 , ItemFactory::getInstance()->get(381, 0, 1)->setCustomName("다음페이지")->setLore([ "해당 아이템을 인벤토리로 옴기면 다음페이지로 이동!.\n인벤토리로 가져가보세요." ]) );
    $inv->setItem( 53 , ItemFactory::getInstance()->get(426, 0, 1)->setCustomName("나가기")->setLore([ "매입 도우미 GUI에서 나갑니다.\n인벤토리로 가져가보세요." ]) );

    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function ShopBuyTaskEvent ($player):void
  {
    $this->getScheduler()->scheduleDelayedTask(new class ($this, $player) extends Task {
      protected $owner;
      public function __construct(MineFarmManager $owner,Player $player) {
        $this->owner = $owner;
        $this->player = $player;
      }
      public function onRun():void
      {
        $this->owner->ShopBuyManagerUI($this->player);
      }
    }, 10);
  }
  public function ShopBuyManagerUI(Player $player):void
  {
    $name = $player->getName ();
    $encode = [
      'type' => 'form',
      'title' => '[ 마인팜 ]',
      'content' => "
      선택한 팜을 매입하겠습니까?

      {$this->pldb ["목록"] [strtolower($name)] ["구매정보"]} 를(을) 선택했습니다.
      ",
      'buttons' => [
        [
          'text' => '매입하기'
        ],
        [
          'text' => '취소하기'
        ]
      ]
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 6574;
    $packet->formData = json_encode($encode);
    $player->getNetworkSession()->sendDataPacket($packet);
  }

  public function onSeeOpen($player):void
  {
    $name = $player->getName ();
    $playerPos = $player->getPosition();
    $inv = LifeInventoryLib::getInstance ()->create("DOUBLE_CHEST", new Position($playerPos->x, $playerPos->y - 2, $playerPos->z, $playerPos->getWorld()), '[ 팜 ] | 구경도우미',$player);

    $i = 0;
    foreach($this->leveldb as $name => $v){
      if ($name != "섬번호") {
        $inv->setItem( $i , ItemFactory::getInstance()->get(386, 0, 1)->setCustomName("{$name}"));
        ++$i;
      }
    }
    LifeInventoryLib::getInstance ()->send($inv, $player);
  }

  public function MoveTask($player, $worldname):void
  {
    $this->getScheduler()->scheduleDelayedTask(new class ($this, $player, $worldname) extends Task {
      protected $owner;
      public function __construct(MineFarmManager $owner,Player $player, $worldname) {
        $this->owner = $owner;
        $this->player = $player;
        $this->worldname = $worldname;
      }
      public function onRun():void {
        $this->owner->MoveTaskEvent($this->player, $this->worldname);
      }
    }, 10);
  }

  public function MoveTaskEvent($player, $worldname) {
    $level = Server::getInstance()->getWorldManager()->getWorldByName($worldname);
    $x = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getX();
    $y = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getY();
    $z = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getZ();
    $player->teleport(new Position((float)$x, (float)$y, (float)$z, $level));
  }

  public function onUnload():void
  {
    foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
      $world = $level->getFolderName();
      if( strpos($world, "MineFarm.") !== false ) {
        if (file_exists ( $this->getServer ()->getDataPath () . "worlds/" . $world )) {
          if (count ($level->getPlayers()) < 1) {
            Server::getInstance()->getWorldManager()->unloadWorld ($level);
          }
        }
      }
    }
  }

  public function addIsland(Player $player, $name, $World)
  {
    if(isset($this->pldb [strtolower($name)])) {
      $player->sendMessage($this->tag() . "이미 마인팜을 보유중입니다.");
      return true;
    } else {
      if (! file_exists ( $this->getServer ()->getDataPath () . "worlds/" . $World )) {
        $player->sendMessage ( $this->tag() . "해당 월드를 찾을 수 없습니다." );
        return true;
      }
      $money = $this->levelmydb [$this->pldb ["목록"] [strtolower($name)] ["구매정보"]];
      MoneyManager::getInstance ()->sellMoney ($name,$money);
      $count = (int)$this->worlddb ["섬번호"];
      $number = $count+1;
      $task = new PasteWorldTask("worlds/".$World, "worlds/MineFarm." . $number);
      Server::getInstance()->getAsyncPool()->submitTaskToWorker($task, 1);
      $player->sendMessage ($this->tag()."정상적으로 마인팜을 구매했습니다.");
      $this->pldb [strtolower($name)] ["섬번호"] = $number;
      $this->worldjoindb ["{$number}"] ["인사말"] = "{$name} 의 마인팜 입니다. 환영합니다.";
      $this->worldjoindb ["{$number}"] ["스폰"] =  "없음";
      $this->worldjoindb ["{$number}"] ["추천"] =  0;
      $this->worldjoindb ["{$number}"] ["레벨"] =  0;
      $this->worldjoindb ["{$number}"] ["점수"] = 0;
      $this->worldjoindb ["{$number}"] ["레벨블럭"] ["1레벨배너"] =  0;
      $this->worldjoindb ["{$number}"] ["레벨블럭"] ["3레벨배너"] =  0;
      $this->worldjoindb ["{$number}"] ["레벨블럭"] ["5레벨배너"] =  0;
      $this->worldjoindb ["{$number}"] ["레벨블럭"] ["10레벨배너"] =  0;
      $this->worldjoindb ["{$number}"] ["레벨블럭"] ["20레벨배너"] =  0;
      $this->worldjoindb ["{$number}"] ["공유목록"] = [];
      $this->worldjoindb ["{$number}"] ["공유최대수"] = 2;
      $this->worldjoindb ["{$number}"] ["차단정보"] = [];
      $this->worldjoindb ["{$number}"] ["방문목록"] = [];
      $this->worldjoindb ["{$number}"] ["추천목록"] = [];
      $this->worldjoindb ["{$number}"] ["상자"] = 0;
      $this->worldjoindb ["{$number}"] ["화로"] = 0;
      $this->worldjoindb ["{$number}"] ["상자최대수"] = 10;
      $this->worldjoindb ["{$number}"] ["화로최대수"] = 8;
      $this->worldjoindb ["{$number}"] ["보호정보"] = "true";
      $this->worldjoindb ["{$number}"] ["잠금정보"] = "false";
      $this->worldjoindb ["{$number}"] ["전투정보"] = "true";
      $this->worldjoindb ["{$number}"] ["팜등급"] = "씨앗";
      $this->rankingdb ["점수랭킹"] [strtolower($name)] = 0;
      $this->rankingdb ["레벨랭킹"] [strtolower($name)] = 0;
      $this->worlddb ["섬번호"] += 1;
      $this->save ();
      return true;
    }
  }

  public function giveIsland(Player $player, $number, $money)
  {
    $name = $player->getName ();
    if(isset($this->pldb [strtolower($name)])) {
      $player->sendMessage($this->tag() . "이미 마인팜을 보유중입니다.");
      return true;
    } else {
      MoneyManager::getInstance ()->sellMoney ($name,$money);
      $player->sendMessage ($this->tag()."정상적으로 마인팜을 구매했습니다.");
      $this->pldb [strtolower($name)] ["섬번호"] = $number;
      $this->worldjoindb ["{$number}"] ["인사말"] = "{$name} 의 마인팜 입니다. 환영합니다.";
      $this->save ();
      foreach($this->pldb as $name => $v){
        if ($name != "목록") {
          if ($this->pldb [$name] ["섬번호"] == $number){
            unset($this->pldb [strtolower($name)]);
            $this->save ();
            return true;
          }
        }
      }
    }
  }

  public function setRemoveisland($name):void
  {
    foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
      $world = $level->getFolderName();
      if ("MineFarm.{$name}" == $world ) {
        Server::getInstance()->getWorldManager()->unloadWorld ($level);
      }
    }
    $dir = $this->getServer ()->getDataPath () . "worlds/MineFarm.{$name}";
    $task = new PasteWorldRemoveTask($dir);
    Server::getInstance()->getAsyncPool()->submitTaskToWorker($task, 1);
  }

  public function UpMineFarmPoint($MineFarmNumber, $point) {
    if (! is_numeric ($MineFarmNumber)) {
      $count = $this->pldb [strtolower($MineFarmNumber)] ["섬번호"];
    } else {
      $count = $MineFarmNumber;
    }
    foreach($this->pldb as $name => $v){
      if ($name != "목록") {
        $number = $this->pldb [$name] ["섬번호"];
        if ($count == $number) {
          $AllPoint = $this->worldjoindb ["{$number}"] ["점수"];
          $time = ConnectTimes::getInstance()->getAllSecond($name);
          $this->rankingdb ["점수랭킹"] [strtolower($name)] = $time;
          $this->worldjoindb ["{$number}"] ["점수"] = $time;
          $this->save ();

          $time = ConnectTimes::getInstance()->getAllSecond($name);
          $level = $this->worldjoindb ["{$number}"] ["레벨"];
          
          $point = (int)$this->worldjoindb ["{$number}"] ["점수"];
          if($this->worldjoindb ["{$number}"] ["팜등급"] == "숲속"){
            if ($this->rankdb ["초원"] <= $point && $level >= 500){
              $this->worldjoindb ["{$number}"] ["상자최대수"] = "제한없음";
              $this->worldjoindb ["{$number}"] ["화로최대수"] = "제한없음";
              $this->worldjoindb ["{$number}"] ["팜등급"] = "초원";
              $this->save ();
              return true;
            }
          }
          if($this->worldjoindb ["{$number}"] ["팜등급"] == "나무"){
            if ($this->rankdb ["숲속"] <= $point && $level >= 400){
              $this->worldjoindb ["{$number}"] ["상자최대수"] += 12;
              $this->worldjoindb ["{$number}"] ["화로최대수"] += 5;
              $this->worldjoindb ["{$number}"] ["팜등급"] = "숲속";
              $this->save ();
              return true;
            }
          }
          if($this->worldjoindb ["{$number}"] ["팜등급"] == "풀잎"){
            if ($this->rankdb ["나무"] <= $point && $level >= 300){
              $this->worldjoindb ["{$number}"] ["상자최대수"] += 10;
              $this->worldjoindb ["{$number}"] ["화로최대수"] += 2;
              $this->worldjoindb ["{$number}"] ["팜등급"] = "나무";
              $this->save ();
              return true;
            }
          }
          if($this->worldjoindb ["{$number}"] ["팜등급"] == "새싹"){
            if ($this->rankdb ["풀잎"] <= $point && $level >= 200){
              $this->worldjoindb ["{$number}"] ["상자최대수"] += 8;
              $this->worldjoindb ["{$number}"] ["화로최대수"] += 3;
              $this->worldjoindb ["{$number}"] ["팜등급"] = "풀잎";
              $this->save ();
              return true;
            }
          }

          if($this->worldjoindb ["{$number}"] ["팜등급"] == "씨앗"){
            if ($this->rankdb ["새싹"] <= $point && $level >= 100){
              $this->worldjoindb ["{$number}"] ["상자최대수"] += 6;
              $this->worldjoindb ["{$number}"] ["화로최대수"] += 2;
              $this->worldjoindb ["{$number}"] ["팜등급"] = "새싹";
              $this->save ();
              return true;
            }
          }
        }
      }
    }
  }

  public function MineFarmMove(Player $player,$name)
  {
    if (! is_numeric ($name)) {
      $number = $this->pldb [strtolower($name)] ["섬번호"];
    } else {
      $number = $name;
    }
    if ($this->worldjoindb ["{$number}"] ["잠금정보"] != "false"){
      if (isset($this->pldb [strtolower($player->getName ())])){
        if ($this->pldb [strtolower($player->getName ())] ["섬번호"] != $number){
          if (!isset($this->worldjoindb ["{$number}"] ["공유목록"] [strtolower($player->getName ())])){
            $player->sendMessage ($this->tag()."해당 섬은 잠금중이며 팜주인 또는 공유자만 방문이 가능합니다.");
            return true;
          }
        }
      }
    } else {
      if (isset($this->pldb [strtolower($player->getName ())])){
        if ($this->pldb [strtolower($player->getName ())] ["섬번호"] != $number){
          if (isset($this->worldjoindb ["{$number}"] ["차단정보"] [strtolower($player->getName ())])) {
            $player->sendMessage ($this->tag()."당신은 해당 섬에서 차단된 플레이어 입니다..");
            return true;
          }
        }
      }
    }
    if ($this->worldjoindb ["{$number}"] ["스폰"] == "없음") {
      $worldname = "MineFarm.{$number}";
      if(Server::getInstance()->getWorldManager()->loadWorld($worldname)) {
        Server::getInstance()->getWorldManager()->loadWorld("MineFarm.".$number);
        $level = Server::getInstance()->getWorldManager()->getWorldByName($worldname);
        $x = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getX();
        $y = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getY();
        $z = Server::getInstance()->getWorldManager()->getWorldByName($worldname)->getSafeSpawn()->getZ();
        $player->teleport(new Position((float)$x, (float)$y, (float)$z, $level));
        $msg = $this->worldjoindb ["{$number}"] ["인사말"];
        $player->sendMessage ($msg);

        if (!isset($this->worldjoindb ["{$number}"] ["방문목록"] ["{$player->getName ()}"])){
          $this->worldjoindb ["{$number}"] ["방문목록"] ["{$player->getName ()}"] = [];
          $this->save ();
          $this->UpMineFarmPoint($number, 20);
          return true;
        }
      }
    } else {
      $worldname = "MineFarm.{$number}";
      if(Server::getInstance()->getWorldManager()->loadWorld($worldname)) {
        Server::getInstance()->getWorldManager()->loadWorld("MineFarm.{$number}");
        $p = explode ( ":", $this->worldjoindb ["{$number}"] ["스폰"] );
        $x = $p[0];
        $y = $p[1];
        $z = $p[2];
        $level = Server::getInstance()->getWorldManager()->getWorldByName($worldname);
        $player->teleport(new Position((float)$x, (float)$y, (float)$z, $level));
        $msg = $this->worldjoindb [$number] ["인사말"];
        $player->sendMessage ($msg);

        if (!isset($this->worldjoindb ["{$number}"] ["방문목록"] ["{$player->getName ()}"])){
          $this->worldjoindb ["{$number}"] ["방문목록"] ["{$player->getName ()}"] = [];
          $this->save ();
          $this->UpMineFarmPoint($number, 20);
          return true;
        }
      }
    }
  }

  public function MineFarmSuggestion(Player $player,$name)
  {
    if (! is_numeric ($name)) {
      $number = $this->pldb [strtolower($name)] ["섬번호"];
    } else {
      $number = $name;
    }
    if (!isset($this->worldjoindb ["{$number}"] ["추천목록"] ["{$player->getName ()}"])){
      $this->worldjoindb ["{$number}"] ["추천목록"] ["{$player->getName ()}"] = [];
      $this->worldjoindb ["{$number}"] ["추천"] +=  1;
      $this->save ();
      $this->UpMineFarmPoint($number, 20);
      return true;
    }
  }

  public function onDisable():void
  {
    $this->save();
  }

  public function save():void
  {
    $this->player->setAll($this->pldb);
    $this->player->save();
    $this->level->setAll($this->leveldb);
    $this->level->save();
    $this->levelmy->setAll($this->levelmydb);
    $this->levelmy->save();
    $this->world->setAll($this->worlddb);
    $this->world->save();
    $this->rank->setAll($this->rankdb);
    $this->rank->save();
    $this->ranking->setAll($this->rankingdb);
    $this->ranking->save();
    $this->shoplist->setAll($this->shoplistdb);
    $this->shoplist->save();
    $this->worldjoin->setAll($this->worldjoindb);
    $this->worldjoin->save();
  }
}

class UnloadTask extends Task
{
  protected $owner = null;
  public function __construct(MineFarmManager $owner) {
    $this->owner = $owner;
  }

  public function onRun():void
  {
    $this->owner->onUnload();
  }
}

class PasteWorldRemoveTask extends AsyncTask {

  private $oldworld;

  public function __construct(String $oldworld){
    $this->oldworld = $oldworld;
  }

  public function onRun():void
  {
    $this->rmdirAll($this->oldworld);
  }

  public function onCompletion():void
  {

  }

  public function rmdirAll($dir):void
  {
    $dirs = dir($dir);
    while(false !== ($entry = $dirs->read())) {
      if(($entry != '.') && ($entry != '..')) {
        if(is_dir($dir.'/'.$entry)) {
          $this->rmdirAll($dir.'/'.$entry);
        } else {
          @unlink($dir.'/'.$entry);
        }
      }
    }
    $dirs->close();
    @rmdir($dir);
  }
}


class PasteWorldTask extends AsyncTask {

  private $oldworld;
  private $newWorld;

  public function __construct(String $oldworld, String $newWorld){
    $this->oldworld = $oldworld;
    $this->newWorld = $newWorld;
  }

  public function onRun():void
  {
    $this->MapRecurseCopy($this->oldworld, $this->newWorld);
  }

  public function onCompletion():void
  {

  }

  public function MapRecurseCopy($src, $dst):void
  {
    $dir = opendir($src);
    @mkdir($dst);
    while(false !== ( $file = readdir($dir)) ) {
      if (( $file != '.' ) && ( $file != '..' )) {
        if ( is_dir($src . '/' . $file) ) {
          $this->MapRecurseCopy($src . '/' . $file, $dst . '/' . $file);
        }else {
          copy($src . '/' . $file,$dst . '/' . $file);
        }
      }
    }
    closedir($dir);
  }
}
