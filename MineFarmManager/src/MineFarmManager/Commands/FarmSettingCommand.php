<?php
declare(strict_types=1);

namespace MineFarmManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use pocketmine\Server;
use MineFarmManager\MineFarmManager;

class FarmSettingCommand extends Command
{

  protected $plugin;

  public function __construct(MineFarmManager $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('팜관리', '팜을 관리하는 명령어 합니다.', '/팜관리');
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
      $sender->sendMessage ($this->plugin->tag()."/팜관리 인사말 ( 메세지 ) < 팜 방문시 플레이어들에게 뛰우는 메세지를 설정합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 스폰설정 < 팜 스폰위치를 설정합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 공유추가 ( 닉네임 ) < 팜을 같이 이용할 공유자를 추가합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 공유해제 < 팜을 같이 이용하던 공유자를 해제합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 보호설정 < 팜 보호정보를 수정합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 잠금설정 < 팜 잠금정보를 수정합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 전투정보설정 < 팜에서의 전투 가능여부를 설정합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 차단추가 ( 닉네임 ) < 원하는 특정 플레이어의 팜 방문을 차단합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 차단해제 < 차단된 플레이어의 팜 방문 차단을 해제합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 판매 ( 금액 ) < 팜을 원하는 금액으로 판매처에 올려둡니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 양도 ( 닉네임 ) < 팜을 다른 플레이어한테 양도 합니다. >");
      $sender->sendMessage ($this->plugin->tag()."/팜관리 공중분해 < 팜을 양도 또는 판매 없이 공중분해 시킵니다. >");
      return true;
    }
    if (!isset($this->plugin->pldb [strtolower($sender->getName())])) {
      $sender->sendMessage ($this->plugin->tag()."당신은 마인팜을 보유하지 않았습니다.");
      return true;
    }
    switch ($args [0]) {
      case "인사말" :
      if (isset($args[1])) {
        $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
        $this->plugin->worldjoindb ["{$number}"] ["인사말"] = "{$args[1]}";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."환영말을 정상적으로 수정했습니다.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."환영말로 설정할 메세지를 적어주세요.");
        return true;
      }
      break;
      case "스폰설정" :
      $pos = $sender->getPosition();
      $x = $pos->getX ();
      $y = $pos->getY ();
      $z = $pos->getZ ();
      $lv = $pos->world->getFolderName();
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      $this->plugin->worldjoindb ["{$number}"] ["스폰"] = $x . ":" . $y . ":" . $z . ":" . $lv;
      $this->plugin->save ();
      $sender->sendMessage ($this->plugin->tag()."플레이어 위치로 스폰을 설정했습니다.");
      break;
      case "공유추가" :
      if (isset($args[1])) {
        if (!isset($this->plugin->pldb ["목록"] [strtolower($args[1])])) {
          $sender->sendMessage ($this->plugin->tag()."{$args[1]} 플레이어는 존재하지 않습니다.");
          return true;
        }
        $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
        $this->plugin->worldjoindb ["{$number}"] ["공유목록"] [strtolower($args[1])] = [];
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."{$args[1]} 플레이어를 마인팜 공유자로 추가했습니다.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."공유할 플레이어의 이름을 적어주세요.");
        return true;
      }
      break;
      case "공유해제" :
      $this->ShareListUI($sender);
      break;
      case "차단추가" :
      if (isset($args[1])) {
        if (!isset($this->plugin->pldb ["목록"] [strtolower($args[1])])) {
          $sender->sendMessage ($this->plugin->tag()."{$args[1]} 플레이어는 존재하지 않습니다.");
          return true;
        }
        $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
        $this->plugin->worldjoindb ["{$number}"] ["차단정보"] [strtolower($args[1])] = [];
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."{$args[1]} 플레이어를 마인팜 차단목록에 추가했습니다.");
        return true;
      } else {
        $sender->sendMessage ($this->plugin->tag()."공유할 플레이어의 이름을 적어주세요.");
        return true;
      }
      break;
      case "차단해제" :
      $this->BlockListUI($sender);
      break;
      case "보호설정" :
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      if ($this->plugin->worldjoindb ["{$number}"] ["보호정보"] == "true") {
        $this->plugin->worldjoindb ["{$number}"] ["보호정보"] = "false";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜 보호를 해제했습니다.");
        return true;
      } else {
        $this->plugin->worldjoindb ["{$number}"] ["보호정보"] = "true";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜 보호를 진행합니다.");
        return true;
      }
      break;
      case "잠금설정" :
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      if ($this->plugin->worldjoindb ["{$number}"] ["잠금정보"] == "true") {
        $this->plugin->worldjoindb ["{$number}"] ["잠금정보"] = "false";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜 잠금을 해제했습니다.");
        return true;
      } else {
        $this->plugin->worldjoindb ["{$number}"] ["잠금정보"] = "true";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜 잠금을 진행합니다.");
        return true;
      }
      break;
      case "전투정보설정" :
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      if ($this->plugin->worldjoindb ["{$number}"] ["전투정보"] == "true") {
        $this->plugin->worldjoindb ["{$number}"] ["전투정보"] = "false";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜에서 전투를 금지 했습니다.");
        return true;
      } else {
        $this->plugin->worldjoindb ["{$number}"] ["전투정보"] = "true";
        $this->plugin->save ();
        $sender->sendMessage ($this->plugin->tag()."마인팜에서 전투가 가능하게 합니다.");
        return true;
      }
      break;
      case "판매" :
      if (!isset($args[1])) {
        $sender->sendMessage ($this->plugin->tag()."판매가로 올릴 금액을 정확하게 적어주세요.");
        return true;
      }
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      if (!isset($this->plugin->worlddb ["판매마인팜"] ["{$number}"])) {
        $sender->sendMessage ($this->plugin->tag()."판매가 {$args[1]} 원으로 판매소에 등록했습니다.");
        $this->plugin->worlddb ["판매마인팜"] ["{$number}"] = $args[1];
        $this->plugin->save ();
        return true;
      }
      break;
      case "양도" :
      if (!isset($args[1])) {
        $sender->sendMessage ($this->plugin->tag()."양도할 플레이어의 이름을 적어주세요.");
        return true;
      }
      if (!isset($this->plugin->pldb ["목록"] [strtolower($args[1])])) {
        $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 존재하지 않습니다.");
        return true;
      }
      if (isset($this->plugin->pldb [strtolower($args[1])] ["섬번호"])) {
        $sender->sendMessage ($this->plugin->tag()."해당 플레이어는 팜을 이미 보유중입니다.");
        return true;
      }
      $sender->sendMessage ($this->plugin->tag()."{$args[1]} 님에게 나의 마인팜을 양도했습니다.");
      $number = $this->plugin->pldb [strtolower($sender->getName ())] ["섬번호"];
      $this->plugin->worldjoindb ["{$number}"] ["인사말"] = "{$args[1]} 의 마인팜 입니다. 환영합니다.";
      $this->plugin->pldb [strtolower($args[1])] ["섬번호"] = $number;

      unset($this->plugin->pldb [strtolower($sender->getName ())]);
      $this->plugin->save ();
      break;
      case "공중분해" :
      foreach (Server::getInstance()->getWorldManager()->getWorlds() as $level) {
        $world = $level->getFolderName();
        if( $world == "MineFarm.{$name}") {
          if (count ($level->getPlayers()) < 1) {
            $sender->sendMessage ($this->plugin->tag()."팜에 아무도 없어야지만 공중분해가 가능합니다.");
            return true;
          }
        }
      }
      $this->plugin->setRemoveisland($sender->getName ());
      unset($this->plugin->pldb [strtolower($sender->getName ())]);
      $sender->sendMessage ($this->plugin->tag()."당신의 팜을 공중분해 진행했습니다.");
      break;
    }
  }

  public function ShareListUI($sender):void
  {
    $arr = [];
    foreach($this->plugin->getFarmShareLists($sender) as $list){
      array_push($arr, array('text' => '- ' . $list . " 님\n터치시 공유 해제가능"));
    }
    $encode = [
      'type' => 'form',
      'title' => '[ 마인팜 ]',
      'content' => "관리 할 공유자를 선택해주세요.",
      'buttons' => $arr
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 6575;
    $packet->formData = json_encode($encode);
    $sender->getNetworkSession()->sendDataPacket($packet);
  }

  public function BlockListUI($sender):void
  {
    $arr = [];
    foreach($this->plugin->getFarmblockLists($sender) as $list){
      array_push($arr, array('text' => '- ' . $list . " 님\n터치시 차단 해제가능"));
    }
    $encode = [
      'type' => 'form',
      'title' => '[ 마인팜 ]',
      'content' => "관리 할 공유자를 선택해주세요.",
      'buttons' => $arr
    ];
    $packet = new ModalFormRequestPacket ();
    $packet->formId = 6576;
    $packet->formData = json_encode($encode);
    $sender->getNetworkSession()->sendDataPacket($packet);
  }
}
