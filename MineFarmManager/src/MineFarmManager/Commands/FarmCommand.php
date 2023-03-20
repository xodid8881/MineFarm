<?php
declare(strict_types=1);

namespace MineFarmManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use MineFarmManager\MineFarmManager;

class FarmCommand extends Command
{
  
  protected $plugin;
  private $chat;
  
  public function __construct(MineFarmManager $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('팜', '팜을 이용하는 명령어 입니다.', '/팜');
  }
  
  public function execute(CommandSender $sender, string $commandLabel, array $args)
  {
    $name = $sender->getName ();
    if (!isset($this->plugin->pldb ["목록"] [strtolower($name)])){
      $this->plugin->pldb ["목록"] [strtolower($name)] ["구매정보"] = "없음";
      $this->plugin->pldb ["목록"] [strtolower($name)] ["페이지"] = 1;
      $this->plugin->pldb ["목록"] [strtolower($name)] ["이용이벤트"] = "없음";
      $this->plugin->save ();
    }
    if( ! isset($args[0] )){
      $sender->sendMessage ($this->plugin->tag());
      $sender->sendMessage ($this->plugin->tag()."/팜 구매 < 팜을 구매합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 이동 ( 닉네임 또는 번호 ) < 팜으로 이동합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 추천 ( 닉네임 또는 번호 ) < 팜을 추천 합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 레벨보기 ( 닉네임 또는 번호 ) < 팜 레벨을 확인합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 매입장 < 팜 매입장을 방문합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 등급 (닉네임 또는 번호) < 팜 등급을 확인 또는 관리합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 순위 < 서버 상위 팜 순위를 확인합니다. >");
      //$sender->sendMessage ($this->plugin->tag()."/팜 점수순위확인 (닉네임 또는 번호) < 팜 점수순위를 확인합니다. >");
      //$sender->sendMessage ($this->plugin->tag()."/팜 레벨순위확인 (닉네임 또는 번호) < 팜 레벨순위를 확인합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜 구경 < 구매하기전 팜을 구경해보세요. >");
      return true;
    }
    switch ($args [0]) {
      case "구매" :
      if (! isset ( $this->chat [$name] )) {
        $this->plugin->onOpen ($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      if (date("YmdHis") - $this->chat [$name] < 3) {
        $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
        return true;
      } else {
        $this->plugin->onOpen ($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      break;
      case "이동" :
      if (!isset( $args[1] )){
        if (isset($this->plugin->pldb [strtolower($sender->getName ())])) {
          $this->plugin->MineFarmMove ($sender, $sender->getName ());
          return true;
        } else {
          $sender->sendMessage ($this->plugin->tag()."당신은 팜을 보유하지 않았습니다.");
          return true;
        }
      } else {
        if (! is_numeric ($args[1])) {
          if (isset($this->plugin->pldb [strtolower($args[1])])) {
            $this->plugin->MineFarmMove ($sender, $args[1]);
            return true;
          } else {
            $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 팜을 보유하지 않았습니다.");
            return true;
          }
        } else {
          foreach($this->plugin->pldb as $name => $v){
            if ($name != "목록") {
              $number = (int)$this->plugin->pldb [$name] ["섬번호"];
              if ((int)$args[1] == $number) {
                $number = (int)$this->plugin->pldb [$name] ["섬번호"];
                $this->plugin->MineFarmMove ($sender, $number);
                return true;
              }
            }
          }
          $sender->sendMessage ($this->plugin->tag()."해당 번호에 팜이 존재하지 않습니다.");
          return true;
        }
      }
      break;
      case "추천" :
      if (!isset( $args[1] )){
        $sender->sendMessage ($this->plugin->tag()."/팜 추천 ( 닉네임 또는 번호 ) < 팜을 추천 합니다. >");
        return true;
      } else {
        if (isset($this->plugin->pldb [strtolower($args[1])])) {
          $sender->sendMessage ($this->plugin->tag()."해당 팜을 추천했습니다.");
          $sender->sendMessage ($this->plugin->tag()."추천은 한번만 가능하며 이후에는 추천을 하여도 추천수가 증가하지 않습니다.");
          $this->plugin->MineFarmSuggestion ($sender, $args[1]);
          return true;
        } else {
          $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 팜을 보유하지 않았습니다.");
          return true;
        }
      }
      break;
      case "레벨보기" :
      if (!isset( $args[1] )){
        if (isset($this->plugin->pldb [strtolower($sender->getName ())])) {
          if (! isset ( $this->chat [$name] )) {
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
          if (date("YmdHis") - $this->chat [$name] < 3) {
            $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
            return true;
          } else {
            $number = (int)$this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
            $this->plugin->LevelSeeGUI ($sender, $number);
            $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
            return true;
          }
        } else {
          $sender->sendMessage ($this->plugin->tag()."당신은 팜을 보유하지 않았습니다.");
          return true;
        }
      } else {
        if (!is_numeric ($args[1])) {
          if (isset($this->plugin->pldb [strtolower($args[1])])) {
            if (!isset($this->plugin->pldb [$args[1]] ["섬번호"])) return;
            $number = (int)$this->plugin->pldb [$args[1]] ["섬번호"];
            if (! isset ( $this->chat [$name] )) {
              $this->plugin->LevelSeeGUI ($sender, $number);
              $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
              return true;
            }
            if (date("YmdHis") - $this->chat [$name] < 3) {
              $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
              return true;
            } else {
              $this->plugin->LevelSeeGUI ($sender, $number);
              $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
              return true;
            }
          } else {
            $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 팜을 보유하지 않았습니다.");
            return true;
          }
        } else {
          foreach($this->plugin->pldb as $name => $v){
            if ($name != "목록") {
              $number = (int)$this->plugin->pldb [$name] ["섬번호"];
              if ((int)$args[1] == $number) {
                $number = (int)$this->plugin->pldb [$name] ["섬번호"];
                $this->plugin->LevelSeeGUI ($sender, $number);
                return true;
              }
            }
          }
          $sender->sendMessage ($this->plugin->tag()."해당 번호에 팜이 존재하지 않습니다.");
          return true;
        }
      }
      break;
      case "매입장" :
      if (! isset ( $this->chat [$name] )) {
        $playerpage = $this->plugin->pldb ["목록"] [strtolower($sender->getName ())] ["페이지"];
        $this->plugin->ShopOpen($sender,$playerpage);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      if (date("YmdHis") - $this->chat [$name] < 3) {
        $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
        return true;
      } else {
        $playerpage = $this->plugin->pldb ["목록"] [strtolower($sender->getName ())] ["페이지"];
        $this->plugin->ShopOpen($sender,$playerpage);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      break;
      case "등급" :
      if (!isset( $args[1] )){
        $sender->sendMessage ($this->plugin->tag()."/팜 등급 (닉네임 또는 번호) < 팜 등급을 확인 또는 관리합니다. >");
        $this->plugin->PlayerRank($sender, $sender->getName ());
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."/팜 등급 (닉네임 또는 번호) < 팜 등급을 확인 또는 관리합니다. >");
        $this->plugin->PlayerRank($sender, $args[1]);
        return true;
      }
      break;
      case "순위" :
      if ($this->plugin->worlddb ["섬번호"] == 0){
        $sender->sendMessage ($this->plugin->tag()."서버에 생성된 팜이 없습니다.");
        $sender->sendMessage ($this->plugin->tag()."팜 생성 컨텐츠를 이용 후 순위를 이용해주세요.");
        return true;
      }
      if (! isset ( $this->chat [$name] )) {
        $this->plugin->PlayerPointGUI($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      if (date("YmdHis") - $this->chat [$name] < 3) {
        $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
        return true;
      } else {
        $this->plugin->PlayerPointGUI($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      break;
      case "점수순위확인" :
      if (!isset( $args[1] )){
        $sender->sendMessage ($this->plugin->tag()."/팜 점수순위확인 (닉네임 또는 번호) < 팜 점수순위를 확인합니다. >");
        $sender->sendMessage ($this->plugin->tag()."확인할 플레이어의 닉네임 또는 팜의 번호를 적어주세요.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."/팜 점수순위확인 (닉네임 또는 번호) < 팜 점수순위를 확인합니다. >");
        $this->plugin->SeePointList($sender, $args[1], "점수");
        return true;
      }
      break;
      case "레벨순위확인" :
      if (!isset( $args[1] )){
        $sender->sendMessage ($this->plugin->tag()."/팜 레벨순위확인 (닉네임 또는 번호) < 팜 레벨순위를 확인합니다. >");
        $sender->sendMessage ($this->plugin->tag()."확인할 플레이어의 닉네임 또는 팜의 번호를 적어주세요.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."/팜 레벨순위확인 (닉네임 또는 번호) < 팜 레벨순위를 확인합니다. >");
        $this->plugin->SeePointList($sender, $args[1], "레벨");
        return true;
      }
      break;
      case "구경" :
      if (! isset ( $this->chat [$name] )) {
        $this->plugin->onSeeOpen ($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      if (date("YmdHis") - $this->chat [$name] < 3) {
        $sender->sendMessage ( $this->plugin->tag() . "이용 쿨타임이 지나지 않아 불가능합니다." );
        return true;
      } else {
        $this->plugin->onSeeOpen ($sender);
        $this->chat [$name] = date("YmdHis",strtotime ("+3 seconds"));
        return true;
      }
      break;
    }
  }
}
