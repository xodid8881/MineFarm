<?php
declare(strict_types=1);

namespace MineFarmManager\Commands;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\network\mcpe\protocol\ModalFormRequestPacket;
use pocketmine\player\Player;
use MineFarmManager\MineFarmManager;

class MyFarmCommand extends Command
{

  protected $plugin;

  public function __construct(MineFarmManager $plugin)
  {
    $this->plugin = $plugin;
    parent::__construct('팜정보', '팜정보 명령어 입니다.', '/팜정보');
  }
  public function onEnable():void
  {
    foreach (array_keys($this->rankingdb ["랭킹"]) as $name) {
    }
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
    foreach($this->plugin->pldb as $Farm => $v){
      if (isset($this->plugin->pldb [strtolower($name)] ["섬번호"])) {
        if ($Farm != "목록") {
          if ($Farm == strtolower($name)) {
            $number = $this->plugin->pldb [strtolower($name)] ["섬번호"];
            $encode = [
              'type' => 'form',
              'title' => '[ 마인팜 ]',
              'content' => "\n\n나의 팜 정보\n\n팜 번호 : {$number} 번\n팜 인사말 : {$this->plugin->worldjoindb [$number] ["인사말"]}\n\n팜 보호정보 : {$this->plugin->worldjoindb [$number] ["보호정보"]}\n팜 잠금정보 : {$this->plugin->worldjoindb [$number] ["잠금정보"]}\n팜 전투정보 : {$this->plugin->worldjoindb [$number] ["전투정보"]}\n\n팜 등급 : {$this->plugin->worldjoindb [$number] ["팜등급"]}",
              'buttons' => [
                [
                  'text' => "< 나가기 >\nUI창을 종료합니다."
                ]
              ]
            ];
            $packet = new ModalFormRequestPacket ();
            $packet->formId = 76565;
            $packet->formData = json_encode($encode);
            $sender->getNetworkSession()->sendDataPacket($packet);
            return true;
          }
        }
      }
    }
    $sender->sendMessage ($this->plugin->tag() . "당신은 팜을 보유하지 않았습니다.");
    return true;
  }
}
