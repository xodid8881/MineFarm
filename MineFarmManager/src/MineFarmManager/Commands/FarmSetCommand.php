<?php
declare(strict_types=1);

namespace MineFarmManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use MineFarmManager\MineFarmManager;
use MoneyManager\MoneyManager;
use pocketmine\permission\DefaultPermissions;
use ConnectTimes\ConnectTimes;
class FarmSetCommand extends Command
{

  protected $plugin;

  public function __construct(MineFarmManager $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('팜설정', '팜의 정보들을 설정하는 명령어 입니다.', '/팜설정');
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
    if (!$sender->hasPermission(DefaultPermissions::ROOT_OPERATOR)) {
      $sender->sendMessage($this->plugin->tag()."권한이 없습니다.");
      return true;
    }

    if( ! isset($args[0] )){
      $sender->sendMessage ($this->plugin->tag());
      $sender->sendMessage ($this->plugin->tag()."/팜설정 랭크리로드 < 서버 팜 순위 링크를 리로드 합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜설정 바이오 ( 팜이름 ) ( 가격 ) < 설정한 이름으로 팜 기본 바이오를 생성합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜설정 등급설정 ( 닉네임 또는 번호 ) ( 등급 ) < 플레이어 또는 번호에 해당하는 섬의 등급을 직접적으로 변경합니다. >");
      
      return true;
    }
    switch ($args [0]) {
      case "랭크리로드" :
      foreach($this->plugin->pldb as $name => $v){
        if ($name != "목록") {
          $number = $this->plugin->pldb [$name] ["섬번호"];
          $time = ConnectTimes::getInstance()->getAllSecond($name);
          $this->plugin->rankingdb ["점수랭킹"] [strtolower($name)] = $time;
          $this->plugin->worldjoindb ["{$number}"] ["점수"] = $time;
          
          $level = $this->plugin->worldjoindb ["{$number}"] ["레벨"];
          $this->plugin->rankingdb ["레벨랭킹"] [strtolower($name)] = $level;
          $this->plugin->worldjoindb ["{$number}"] ["레벨"] =  $level;

        }
      }
      $this->plugin->save ();
      $sender->sendMessage ( $this->plugin->tag() . "정상적으로 랭크를 리로드 했습니다." );
      break;
      case "바이오" :
      if (!isset($args[1])) {
        $sender->sendMessage ( $this->plugin->tag() . "설정할 팜이름을 적어주세요." );
        return true;
      }
      if (!isset($args[2])) {
        $sender->sendMessage ( $this->plugin->tag() . "설정할 팜의 가격을 적어주세요." );
        return true;
      }
      if (! is_numeric ($args[2])) {
        $sender->sendMessage ( $this->plugin->tag() . "숫자를 이용 해야됩니다. " );
        return;
      }
      $money = MoneyManager::getInstance ()->getKoreanMoney ($args[2]);
      $sender->sendMessage ( $this->plugin->tag() . "{$args[1]} 이름으로 마인팜의 월드를 생성했습니다." );
      $sender->sendMessage ( $this->plugin->tag() . "{$args[1]} 마인팜의 가격은 {$money} 입니다." );
      $pos = $sender->getPosition();
      $world = $pos->world;
      $this->plugin->leveldb ["{$args[1]}"] = $pos->world->getFolderName();
      $this->plugin->levelmydb ["{$args[1]}"] = $args[2];
      $this->plugin->save ();
      break;
      case "등급설정" :
      if (!isset($args[1])) {
        $sender->sendMessage ( $this->plugin->tag() . "설정할 팜번호 또는 플레이어 닉네임을 적어주세요." );
        return true;
      }
      if (!isset($args[2])) {
        $sender->sendMessage ( $this->plugin->tag() . "설정할 팜의 등급을 적어주세요." );
        return true;
      }
      if (! is_numeric ($args[1])) {
        if (isset($this->plugin->pldb [strtolower($args[1])])) {
          if ($args[2] == "씨앗"){
            $rank = "씨앗";
            $rankpoint = 0;
          } else if ($args[2] == "새싹"){
            $rank = "새싹";
            $rankpoint = 250;
          } else if ($args[2] == "풀잎"){
            $rank = "풀잎";
            $rankpoint = 750;
          } else if ($args[2] == "나무"){
            $rank = "나무";
            $rankpoint = 1500;
          } else if ($args[2] == "숲속"){
            $rank = "숲속";
            $rankpoint = 2750;
          } else if ($args[2] == "초원"){
            $rank = "초원";
            $rankpoint = 5000;
          }
          $number = $this->plugin->pldb [strtolower($args[1])] ["섬번호"];
          $this->plugin->worldjoindb ["{$number}"] ["팜등급"] = $rank;
          $this->plugin->worldjoindb ["{$number}"] ["점수"] = $rankpoint;
          $this->plugin->save ();
          return true;
        } else {
          $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 마인팜을 보유하지 않았습니다.");
          return true;
        }
      } else {
        $sender->sendMessage ($this->plugin->tag()."해당 번호에 마인팜이 존재하지 않습니다.");
        return true;
      }
    }
  }
}
