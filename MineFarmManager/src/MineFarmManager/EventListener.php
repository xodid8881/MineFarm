<?php
declare(strict_types=1);

namespace MineFarmManager;

use pocketmine\event\Listener;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\player\Player;
use pocketmine\Server;

use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;

use pocketmine\network\mcpe\protocol\InventoryTransactionPacket;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\utils\TextFormat;
use pocketmine\item\Item;
use pocketmine\item\ItemFactory;
use pocketmine\item\ItemIds;
use pocketmine\world\Position;
use pocketmine\world\World;
use pocketmine\world\WorldManager;

use MoneyManager\MoneyManager;
use pocketmine\inventory\transaction\action\SlotChangeAction;
use pocketmine\event\inventory\InventoryCloseEvent;
use pocketmine\inventory\ContainerInventory;

use pocketmine\network\mcpe\protocol\ContainerClosePacket;

use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityDamageByEntityEvent;

use LifeInventoryLib\InventoryLib\InvLibManager;
use LifeInventoryLib\InventoryLib\LibInvType;
use LifeInventoryLib\InventoryLib\InvLibAction;
use LifeInventoryLib\InventoryLib\SimpleInventory;
use LifeInventoryLib\InventoryLib\LibInventory;

use pocketmine\permission\DefaultPermissions;

class EventListener implements Listener
{

  protected $plugin;

  public function __construct(MineFarmManager $plugin)
  {
    $this->plugin = $plugin;
  }

  public function OnJoin (PlayerJoinEvent $event): void
  {
    $player = $event->getPlayer ();
    $name = $player->getName ();
    if (!isset($this->plugin->pldb ["목록"] [strtolower($name)])){
      $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"] = "없음";
      $this->plugin->pldb ["목록"] [strtolower($name)] ["페이지"] = 1;
      $this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] = "없음";
      $this->plugin->save ();
    }
  }

  public function OnInteract(PlayerInteractEvent $event) {
    $player = $event->getPlayer ();
    $name = $player->getName ();
    $item = $player->getInventory()->getItemInHand();
    if ($item->getId () == 437) {
      if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
        $item = ItemFactory::getInstance()->get(446, 9, 1)->setCustomName("§l§b1.Lv §rUP 배너")->setLore([ "§7팜의 레벨을 1.Lv 상승시켜줍니다.\n§7사용방법 : 팜 설치를 진행하면 됩니다." ]);
        $player->getInventory ()->addItem ( $item );
        $item = ItemFactory::getInstance()->get(446, 12, 1)->setCustomName("§l§b3.Lv §rUP 배너")->setLore([ "§7팜의 레벨을 3.Lv 상승시켜줍니다.\n§7사용방법 : 팜 설치를 진행하면 됩니다." ]);
        $player->getInventory ()->addItem ( $item );
        $item = ItemFactory::getInstance()->get(446, 14, 1)->setCustomName("§l§b5.Lv §rUP 배너")->setLore([ "§7팜의 레벨을 5.Lv 상승시켜줍니다.\n§7사용방법 : 팜 설치를 진행하면 됩니다." ]);
        $player->getInventory ()->addItem ( $item );
        $item = ItemFactory::getInstance()->get(446, 6, 1)->setCustomName("§l§b10.Lv §rUP 배너")->setLore([ "§7팜의 레벨을 10.Lv 상승시켜줍니다.\n§7사용방법 : 팜 설치를 진행하면 됩니다." ]);
        $player->getInventory ()->addItem ( $item );
        $item = ItemFactory::getInstance()->get(446, 15, 1)->setCustomName("§l§b20.Lv §rUP 배너")->setLore([ "§7팜의 레벨을 20.Lv 상승시켜줍니다.\n§7사용방법 : 팜 설치를 진행하면 됩니다." ]);
        $player->getInventory ()->addItem ( $item );
        return true;
      }
    }
    $pos = $player->getPosition();
    $world = $pos->world;
    $tag = explode ( ".", $pos->world->getFolderName() );
    $worldname = $tag[0];
    if ($worldname == "MineFarm") {
      $number = $tag[1];
      if (isset($this->plugin->pldb [strtolower($name)] ["섬번호"])) {
        $count = $this->plugin->pldb [strtolower($name)] ["섬번호"];
        if ($count == $number) {
          if ($item->getCustomName () == "§l§b1.Lv §rUP 배너") {
            $event->cancel ();
            $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
            $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level+1;
            $this->plugin->worldjoindb ["{$number}"] ["레벨"] +=  1;
            $this->plugin->worldjoindb ["{$number}"] ["레벨블럭"] ["1레벨배너"] +=  1;

            $this->plugin->save ();
            $player->sendMessage ( $this->plugin->tag() . "팜 레벨을 1.Lv 상승 시켰습니다." );
            $player->getInventory ()->removeItem ( $item->setCount(1) );
            return true;
          }
          if ($item->getCustomName () == "§l§b3.Lv §rUP 배너") {
            $event->cancel ();
            $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
            $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level+3;
            $this->plugin->worldjoindb ["{$number}"] ["레벨"] +=  3;
            $this->plugin->worldjoindb ["{$number}"] ["레벨블럭"] ["3레벨배너"] +=  1;
            $this->plugin->save ();
            $player->sendMessage ( $this->plugin->tag() . "팜 레벨을 3.Lv 상승 시켰습니다." );
            $player->getInventory ()->removeItem ( $item->setCount(1) );
            return true;
          }
          if ($item->getCustomName () == "§l§b5.Lv §rUP 배너") {
            $event->cancel ();
            $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
            $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level+5;
            $this->plugin->worldjoindb ["{$number}"] ["레벨"] +=  5;
            $this->plugin->worldjoindb ["{$number}"] ["레벨블럭"] ["5레벨배너"] +=  1;
            $this->plugin->save ();
            $player->sendMessage ( $this->plugin->tag() . "팜 레벨을 5.Lv 상승 시켰습니다." );
            $player->getInventory ()->removeItem ( $item->setCount(1) );
            return true;
          }
          if ($item->getCustomName () == "§l§b10.Lv §rUP 배너") {
            $event->cancel ();
            $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
            $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level+10;
            $this->plugin->worldjoindb ["{$number}"] ["레벨"] +=  10;
            $this->plugin->worldjoindb ["{$number}"] ["레벨블럭"] ["10레벨배너"] +=  1;
            $this->plugin->save ();
            $player->sendMessage ( $this->plugin->tag() . "팜 레벨을 10.Lv 상승 시켰습니다." );
            $player->getInventory ()->removeItem ( $item->setCount(1) );
            return true;
          }
          if ($item->getCustomName () == "§l§b20.Lv §rUP 배너") {
            $event->cancel ();
            $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
            $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level+20;
            $this->plugin->worldjoindb ["{$number}"] ["레벨"] +=  20;
            $this->plugin->worldjoindb ["{$number}"] ["레벨블럭"] ["20레벨배너"] +=  1;
            $this->plugin->save ();
            $player->sendMessage ( $this->plugin->tag() . "팜 레벨을 20.Lv 상승 시켰습니다." );
            $player->getInventory ()->removeItem ( $item->setCount(1) );
            return true;
          }
        } else {
          if ($item->getCustomName () == "§l§b1.Lv §rUP 배너" or $item->getCustomName () == "§l§b3.Lv §rUP 배너" or $item->getCustomName () == "§l§b5.Lv §rUP 배너" or $item->getCustomName () == "§l§b10.Lv §rUP 배너" or $item->getCustomName () == "§l§b20.Lv §rUP 배너") {
            $event->cancel ();
            $player->sendMessage ( $this->plugin->tag() . "자기 자신의 팜에서 사용해주세요." );
            return true;
          }
        }
      }
    } else {
      if ($item->getCustomName () == "§l§b1.Lv §rUP 배너" or $item->getCustomName () == "§l§b3.Lv §rUP 배너" or $item->getCustomName () == "§l§b5.Lv §rUP 배너" or $item->getCustomName () == "§l§b10.Lv §rUP 배너" or $item->getCustomName () == "§l§b20.Lv §rUP 배너") {
        $event->cancel ();
        $player->sendMessage ( $this->plugin->tag() . "팜 월드에서만 이용이 가능합니다." );
        return true;
      }
    }
  }

  public function onTransaction(InventoryTransactionEvent $event)
  {
    $transaction = $event->getTransaction();
    $player = $transaction->getSource ();
    $name = $player->getName ();
    foreach($transaction->getActions() as $action){
      if($action instanceof SlotChangeAction){
        $inv = $action->getInventory();
        if($inv instanceof LibInventory){
          $slot = $action->getSlot ();
          $item = $inv->getItem ($slot);
          $id = $item->getId ();
          $damage = $item->getMeta ();
          $itemname = $item->getCustomName ();
          $nbt = $item->jsonSerialize ();
          if ($inv->getTitle() == "[ 팜 ] | 해당 플레이어 팜정보"){
            if ($id == 446){
              $event->cancel ();
              return true;
            }
          }
          if ($inv->getTitle() == '[ 팜 ] | 순위'){
            $event->cancel ();
            if ( $itemname == "§l§b레벨순서" ) {
              $i = 0;
              while ($i <= 8){
                $cup = explode ( " ", "14 22 23 24 30 31 32 33 34" );
                $number = (int)$cup[$i];
                $inv->setItem( $number , ItemFactory::getInstance()->get(199, 0, 1)->setCustomName("§l§7공백"));
                ++$i;
              }
              arsort($this->plugin->rankingdb ["레벨랭킹"]);
              $count = 0;
              foreach (array_keys($this->plugin->rankingdb ["레벨랭킹"]) as $name) {
                if (0 <= $count and 8 >= $count) {
                  $number = $this->plugin->pldb [$name] ["섬번호"];
                  $point = (int)$this->plugin->worldjoindb ["{$number}"] ["점수"];
                  $rank = $this->plugin->worldjoindb ["{$number}"] ["팜등급"];
                  $Suggestion = $this->plugin->worldjoindb ["{$number}"] ["추천"];
                  $Level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
                  $arr = [];
                  foreach($this->plugin->getFarmRankShareLists($name) as $list) {
                    $arr[] = "§f•" . $list . "\n";
                  }
                  $randcount = $count+1;
                  $cup = explode ( " ", " 14 22 23 24 30 31 32 33 34" );
                  $number = (int)$cup[$randcount];
                  $inv->setItem( $number , ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§l§f{$name} §b님")->setLore([ "§b§l| §f순위 §b- §f" . $randcount . " §b위\n§b§l| §f레벨 §b- §f{$Level}§b.Lv\n§b§l| §f추천수 §b- §f{$Suggestion} §b회\n§b§l| §f점수 §b- §f{$point} §b점\n§b§l| §f등급 §b- §f{$rank} 등급\n\n§b§l| §f공유자 목록 §b- §f\n" . implode("", $arr) ]));
                  $count++;
                }
              }
              return true;
            }
            if ( $itemname == "§l§b점수순서" ) {
              $i = 0;
              while ($i <= 8){
                $cup = explode ( " ", "14 22 23 24 30 31 32 33 34" );
                $number = (int)$cup[$i];
                $inv->setItem( $number , ItemFactory::getInstance()->get(199, 0, 1)->setCustomName("§l§7공백"));
                ++$i;
              }
              arsort($this->plugin->rankingdb ["점수랭킹"]);
              $count = 0;
              foreach (array_keys($this->plugin->rankingdb ["점수랭킹"]) as $name) {
                if (0 <= $count and 8 >= $count) {
                  if (isset($this->plugin->pldb [$name] ["섬번호"])) {
                    $number = $this->plugin->pldb [$name] ["섬번호"];
                    $point = (int)$this->plugin->worldjoindb ["{$number}"] ["점수"];
                    $rank = $this->plugin->worldjoindb ["{$number}"] ["팜등급"];
                    $Suggestion = $this->plugin->worldjoindb ["{$number}"] ["추천"];
                    $Level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
                    $arr = [];
                    foreach($this->plugin->getFarmRankShareLists($name) as $list) {
                      $arr[] = "§f•" . $list . "\n";
                    }
                    $randcount = $count+1;
                    $cup = explode ( " ", " 14 22 23 24 30 31 32 33 34" );
                    $number = (int)$cup[$randcount];
                    $inv->setItem( $number , ItemFactory::getInstance()->get(397, 3, 1)->setCustomName("§l§f{$name} §b님")->setLore([ "§b§l| §f순위 §b- §f" . $randcount . " §b위\n§b§l| §f레벨 §b- §f{$Level}§b.Lv\n§b§l| §f추천수 §b- §f{$Suggestion} §b회\n§b§l| §f점수 §b- §f{$point} §b점\n§b§l| §f등급 §b- §f{$rank} 등급\n\n§b§l| §f공유자 목록 §b- §f\n" . implode("", $arr) ]));
                  }
                  $count++;
                }
              }
              return true;
            }
          }
          if ($inv->getTitle() == '[ 팜 ] | 구경도우미'){
            $event->cancel ();
            $inv->onClose($player);
            if ($itemname == "") return;
            $worldname = $this->plugin->leveldb ["{$itemname}"];
            Server::getInstance()->getWorldManager()->loadWorld ( $worldname );
            $this->plugin->MoveTask ($player, $worldname);
            return true;
          }
          if ($inv->getTitle() == '[ 팜 ] | 구입도우미'){
            $event->cancel ();
            $inv->onClose($player);
            $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"] = $itemname;
            $this->plugin->save ();
            $this->plugin->BuyTaskEvent ($player);
            return true;
          }
          if ($inv->getTitle() == '[ 팜 ] | 매입 도우미'){
            $event->cancel ();
            if ( $itemname == "구매모드" ) {
              $this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] = "구매모드";
              $this->plugin->save ();
              return true;
            }
            if ( $itemname == "이동모드" ) {
              $this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] = "이동모드";
              $this->plugin->save ();
              return true;
            }
            if ( $itemname == "이전페이지" ) {
              $inv->onClose($player);
              $this->plugin->pldb ["목록"] [strtolower($name)] ["페이지"] -= 1;
              $this->plugin->save ();
              $this->plugin->ShopOpen($player);
              return true;
            }
            if ( $itemname == "다음페이지" ) {
              $inv->onClose($player);
              $this->plugin->pldb ["목록"] [strtolower($name)] ["페이지"] += 1;
              $this->plugin->save ();
              $this->plugin->ShopOpen($player);
              return true;
            }
            if ( $itemname == "나가기" ) {
              $inv->onClose($player);
              return true;
            }
            if ($this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] == "구매모드"){
              $inv->onClose($player);
              $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"] = $itemname;
              $this->plugin->save ();
              $this->plugin->ShopBuyTaskEvent ($player);
              return true;
            } else if ($this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] == "이동모드"){
              $inv->onClose($player);
              $this->plugin->MineFarmMove($player,$itemname);
              return true;
            }
            $inv->onClose($player);
            return true;
          }
        }
      }
    }
  }
  public function onPacket(DataPacketReceiveEvent $event)
  {
    $packet = $event->getPacket();
    $player = $event->getOrigin()->getPlayer();
    if($packet instanceof ModalFormResponsePacket) {
      $name = $player->getName();
      $id = $packet->formId;
      if($packet->formData == null) {
        return true;
      }
      $data = json_decode($packet->formData, true);
      if ($id === 6573) {
        if ($data === 0) {
          $itemname = $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"];
          $buymoney = $this->plugin->levelmydb [$itemname];
          if(MoneyManager::getInstance ()->getMoney ($name) >= $buymoney){
            $itemname = $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"];
            $number = $this->plugin->leveldb ["{$itemname}"];
            $this->plugin->addIsland ($player, $name, $number);
            return true;
          } else {
            $player->sendMessage ($this->plugin->tag() . $itemname . "마인팜을 구매하기 위한 비용이 부족합니다.");
            return true;
          }
        }
        if ($data === 1) {
          $player->sendMessage( $this->plugin->tag() . '이용을 종료했습니다.');
          return true;
        }
      }
      if ($id === 6574) {
        if ($data === 0) {
          $itemname = $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"];
          if ($itemname == "") return;
          $money = (int)$this->plugin->worlddb ["판매마인팜"] ["{$itemname}"];
          if(MoneyManager::getInstance ()->getMoney ($name) >= $money){
            $number = $this->plugin->leveldb ["{$itemname}"];
            $this->plugin->giveIsland ($player, $itemname, $money);
            return true;
          } else {
            $player->sendMessage ($this->plugin->tag() . "마인팜을 구매하기 위한 비용이 부족합니다.");
            return true;
          }
        }
        if ($data === 1) {
          $player->sendMessage( $this->plugin->tag() . '이용을 종료했습니다.');
          return true;
        }
      }
      if ($id === 6575) {
        if($data !== null){
          $arr = [];
          foreach($this->plugin->getFarmShareLists($player) as $FarmShareName){
            array_push($arr, $FarmShareName);
          }
          $number = $this->plugin->pldb [strtolower($name)] ["섬번호"];
          unset($this->plugin->worldjoindb ["{$number}"] ["공유목록"] [$arr[$data]]);
          $this->plugin->save ();
          $player->sendMessage ($this->plugin->tag() . "해당 플레이어를 공유목록에서 제거했습니다.");
          return true;
        }
      }
      if ($id === 6576) {
        if($data !== null){
          $arr = [];
          foreach($this->plugin->getFarmblockLists($player) as $FarmShareName){
            array_push($arr, $FarmShareName);
          }
          $number = $this->plugin->pldb [strtolower($name)] ["섬번호"];
          unset($this->plugin->worldjoindb ["{$number}"] ["차단정보"] [$arr[$data]]);
          $this->plugin->save ();
          $player->sendMessage ($this->plugin->tag() . "해당 플레이어의 차단을 취소했습니다.");
          return true;
        }
      }
    }
  }

  public function onBlockBreak(BlockBreakEvent $event)
  {
    $player = $event->getPlayer();
    $name = $player->getName ();
    $block = $event->getBlock();
    $pos = $player->getPosition();
    $world = $pos->world;
    $tag = explode ( ".", $pos->world->getFolderName() );
    $worldname = $tag[0];
    if ($worldname == "MineFarm") {
      if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
        return true;
      }
      $number = $tag[1];
      if (isset($this->plugin->pldb [strtolower($name)] ["섬번호"])) {
        $count = $this->plugin->pldb [strtolower($name)] ["섬번호"];
        if ($count == $number) {
          if ($block->getId () == 54) {
            if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
              return true;
            } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] != 0){
              $this->plugin->worldjoindb ["{$number}"] ["상자"] -= 1;
              $this->plugin->save ();
              return true;
            }
          }
          if ($block->getId () == 61) {
            if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
              return true;
            } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] != 0){
              $this->plugin->worldjoindb ["{$number}"] ["화로"] -= 1;
              $this->plugin->save ();
              return true;
            }
          }
          return true;
        }
      }
      if(isset($this->plugin->worldjoindb ["{$number}"] ["공유목록"] [strtolower($name)])){
        if ($block->getId () == 54) {
          if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] != 0){
            $this->plugin->worldjoindb ["{$number}"] ["상자"] -= 1;
            $this->plugin->save ();
            return true;
          }
        }
        if ($block->getId () == 61) {
          if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] != 0){
            $this->plugin->worldjoindb ["{$number}"] ["화로"] -= 1;
            $this->plugin->save ();
            return true;
          }
        }
        return true;
      }
      if ($this->plugin->worldjoindb ["{$number}"] ["보호정보"] != "true"){
        if ($block->getId () == 54) {
          if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] != 0){
            $this->plugin->worldjoindb ["{$number}"] ["상자"] -= 1;
            $this->plugin->save ();
            return true;
          }
        }
        if ($block->getId () == 61) {
          if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] != 0){
            $this->plugin->worldjoindb ["{$number}"] ["화로"] -= 1;
            $this->plugin->save ();
            return true;
          }
        }
        return true;
      }
      $event->cancel ();
      $player->sendTip( $this->plugin->tag() . '당신의 팜이 아니며 또한 공유 받은 대상자도 아닙니다.');
      return true;
    }
  }

  public function onBlockPlace(BlockPlaceEvent $event)
  {
    $player = $event->getPlayer();
    $name = $player->getName ();
    $block = $event->getBlock ();
    $pos = $player->getPosition();
    $world = $pos->world;
    $tag = explode ( ".", $pos->world->getFolderName() );
    $worldname = $tag[0];
    if ($worldname == "MineFarm") {
      if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
        return true;
      }
      $number = $tag[1];
      if (isset($this->plugin->pldb [strtolower($name)] ["섬번호"])) {
        if (!is_null($this->plugin->CheckFarmXZ($event,$number))){
          return true;
        }
        if (!is_null($this->plugin->CheckFarmY($event,$number))){
          return true;
        }
        $count = $this->plugin->pldb [strtolower($name)] ["섬번호"];
        if ($count == $number) {
          $maxB = $this->plugin->worldjoindb ["{$number}"] ["상자최대수"];
          $maxF = $this->plugin->worldjoindb ["{$number}"] ["화로최대수"];
          if ($block->getId () == 54) {
            if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
              return true;
            } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] < $maxB){
              $this->plugin->worldjoindb ["{$number}"] ["상자"] += 1;
              $this->plugin->save ();
              return true;
            } else {
              $event->cancel ();
              $player->sendMessage ($this->plugin->tag() . "해당 팜의 상자 최대 갯수는 이미 달성했습니다.");
              return true;
            }
          }
          if ($block->getId () == 61) {
            if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
              return true;
            } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] < $maxB){
              $this->plugin->worldjoindb ["{$number}"] ["화로"] += 1;
              $this->plugin->save ();
              return true;
            } else {
              $event->cancel ();
              $player->sendMessage ($this->plugin->tag() . "해당 팜의 화로 최대 갯수는 이미 달성했습니다.");
              return true;
            }
          }
          return true;
        }
      }
      if(isset($this->plugin->worldjoindb ["{$number}"] ["공유목록"] [strtolower($name)])){
        if (!is_null($this->plugin->CheckFarmXZ($event,$number))){
          return true;
        }
        if (!is_null($this->plugin->CheckFarmY($event,$number))){
          return true;
        }
        $maxB = $this->plugin->worldjoindb ["{$number}"] ["상자최대수"];
        $maxF = $this->plugin->worldjoindb ["{$number}"] ["화로최대수"];

        if ($block->getId () == 54) {
          if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] < $maxB){
            $this->plugin->worldjoindb ["{$number}"] ["상자"] += 1;
            $this->plugin->save ();
            return true;
          } else {
            $event->cancel ();
            $player->sendMessage ($this->plugin->tag() . "해당 팜의 상자 최대 갯수는 이미 달성했습니다.");
            return true;
          }
        }

        if ($block->getId () == 61) {
          if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] < $maxB){
            $this->plugin->worldjoindb ["{$number}"] ["화로"] += 1;
            $this->plugin->save ();
            return true;
          } else {
            $event->cancel ();
            $player->sendMessage ($this->plugin->tag() . "해당 팜의 화로 최대 갯수는 이미 달성했습니다.");
            return true;
          }
        }
        return true;
      }
      if ($this->plugin->worldjoindb ["{$number}"] ["보호정보"] != "true"){
        if (!is_null($this->plugin->CheckFarmXZ($event,$number))){
          return true;
        }
        if (!is_null($this->plugin->CheckFarmY($event,$number))){
          return true;
        }
        $maxB = $this->plugin->worldjoindb ["{$number}"] ["상자최대수"];
        $maxF = $this->plugin->worldjoindb ["{$number}"] ["화로최대수"];

        if ($block->getId () == 54) {
          if($this->plugin->worldjoindb ["{$number}"] ["상자"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["상자"] < $maxB){
            $this->plugin->worldjoindb ["{$number}"] ["상자"] += 1;
            $this->plugin->save ();
            return true;
          } else {
            $event->cancel ();
            $player->sendMessage ($this->plugin->tag() . "해당 팜의 상자 최대 갯수는 이미 달성했습니다.");
            return true;
          }
        }

        if ($block->getId () == 61) {
          if($this->plugin->worldjoindb ["{$number}"] ["화로"] == "제한없음"){
            return true;
          } else if($this->plugin->worldjoindb ["{$number}"] ["화로"] < $maxB){
            $this->plugin->worldjoindb ["{$number}"] ["화로"] += 1;
            $this->plugin->save ();
            return true;
          } else {
            $event->cancel ();
            $player->sendMessage ($this->plugin->tag() . "해당 팜의 화로 최대 갯수는 이미 달성했습니다.");
            return true;
          }
        }
        return true;
      }
      $event->cancel ();
      $player->sendTip( $this->plugin->tag() . '당신의 팜이 아니며 또한 공유 받은 대상자도 아닙니다.');
      return true;
    }
  }

  public function onPlayerInteract(PlayerInteractEvent $event)
  {
    $player = $event->getPlayer();
    $name = $player->getName ();

    $pos = $player->getPosition();
    $world = $pos->world;
    $tag = explode ( ".", $pos->world->getFolderName() );
    $worldname = $tag[0];
    if ($worldname == "MineFarm") {
      if ($player->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
        return true;
      }
      $number = $tag[1];
      if (isset($this->plugin->pldb [strtolower($name)] ["섬번호"])) {
        $count = $this->plugin->pldb [strtolower($name)] ["섬번호"];
        if ($count == $number) {
          return true;
        }
      }
      if(isset($this->plugin->worldjoindb ["{$number}"] ["공유목록"] [strtolower($name)])){
        return true;
      }
      if ($this->plugin->worldjoindb ["{$number}"] ["보호정보"] != "true"){
        return true;
      }
      $event->cancel ();
      $player->sendTip( $this->plugin->tag() . '당신의 팜이 아니며 또한 공유 받은 대상자도 아닙니다.');
      return true;
    }
  }

  public function EntityDamage(EntityDamageByEntityEvent $event)
  {
    $entity = $event->getEntity();
    $damager = $event->getDamager();
    if ($damager instanceof Player) {
      if ($entity instanceof Player) {

        $pos = $damager->getPosition();
        $world = $pos->world;
        $tag = explode ( ".", $pos->world->getFolderName() );
        $worldname = $tag[0];
        if ($worldname == "MineFarm") {
          $number = $tag[1];
          if ($this->plugin->worldjoindb [$number] ["전투정보"] != "true"){
            return true;
          }
          $event->cancel ();
          $damager->sendTip( $this->plugin->tag() . '해당 팜의 전투는 금지되어 있습니다.');
          return true;
        }
      }
    }
  }
}
