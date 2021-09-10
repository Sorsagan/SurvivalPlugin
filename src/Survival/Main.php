<?php

namespace Survival;

use pocketmine\item\Item;
use pocketmine\level\DustParticle;
use pocketmine\Server;
use pocketmine\Player;

use pocketmine\plugin\PluginBase;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

use pocketmine\math\Vector3;
use pocketmine\scheduler\Task;
use pocketmine\utils\TextFormat;
use pocketmine\event\Listener;
use pocketmine\form\Form;
use pocketmine\network\mcpe\protocol\ActorEventPacket;
use pocketmine\network\mcpe\protocol\LevelEventPacket;
use pocketmine\network\mcpe\protocol\OnScreenTextureAnimationPacket;
use jojoe77777\FormAPI;
class Main extends PluginBase implements Listener
{
    public function onLoad()
    {
        $this->getLogger()->info("onLoad() has been called!");
    }

    public function onEnable()
    {
          $this->getServer()
            ->getPluginManager()
            ->registerEvents($this, $this);
        $this->getLogger()->info(TextFormat::GREEN . "SurvivalPlugin enabled!");
    }

    public function onDisable()
    {
        $this->getLogger()->info(TextFormat::RED . "SurvivalPlugin disabled!");
    }
    public function onCommand(
        CommandSender $sender,
        Command $cmd,
        string $label,
        array $args
    ): bool {
        switch ($cmd->getName()) {
            case "repair":
                if ($sender instanceof Player) {
                    $item = $sender->getInventory()->getItemInHand();
                    if ($item->getDamage() > 0) {
                        $item->setDamage(0);
                        $sender->getInventory()->setItemInHand($item);
                        $sender->sendPopup(
                            TextFormat::BLUE . "Item succesfully repaired!"
                        );
                        $this->showTotemEffect($sender);
                    } else {
                        $sender->sendPopup(
                            TextFormat::RED . "Item does not have any damage!"
                        );
                    }
                } else {
                    $sender->sendMessage("you arent hooman");
                }
                break;

            case "heal":
                if ($sender instanceof Player) {
                    $effectId = 10;
                    $sender->setHealth(20);
                    $sender->sendPopup(
                        TextFormat::GREEN . "Your hearts restored!"
                    );
                    $this->showOnScreenAnimation($sender, $effectId);
                } else {
                    $sender->sendMessage("you arent hooman");
                }
                break;
            case "food":
                if ($sender instanceof Player) {
                    $effectId = 17;
                    $sender->setFood($sender->getMaxFood());
                    $sender->sendPopup(
                        TextFormat::BLUE . "Your hunger bars restored!"
                    );
                    $this->showOnScreenAnimation($sender, $effectId);
                } else {
                    $sender->sendMessage("you arent hooman");
                }
                break;

            case "form":
                if ($sender instanceof Player) {
                    $this->openMyForm($sender);
                } else {
                    $sender->sendMessage("you arent hooman");
                }
                break;
        }

        return true;
    }
    public function showTotemEffect(Player $player)
    {
        $player->getInventory()->getItemInHand(Item::get(450, 0, 1));
        $player->broadcastEntityEvent(ActorEventPacket::CONSUME_TOTEM);
        $pk = new LevelEventPacket();
        $pk->evid = LevelEventPacket::EVENT_SOUND_TOTEM;
        $pk->position = $player->add(0, $player->eyeHeight, 0);
        $pk->data = 0;
        $player->dataPacket($pk);
        $player->getInventory()->getItemInHand(Item::get(0, 0, 1));
    }
    public function showOnScreenAnimation(Player $player, int $effectId)
    {
        $packet = new OnScreenTextureAnimationPacket();
        $packet->effectId = $effectId;
        $player->sendDataPacket($packet);
    }
    public function openMyForm(Player $player)
    {
        $api = $this->getServer()->getPluginManager()->getPlugin("FormAPI");
        $form = $api->createSimpleForm(function (
            Player $player,
            int $data = null
        ) {
            $result = $data;
            if ($result === null) {
                return true;
            }
            switch ($result) {
                case 0:
                  if ($player instanceof Player) {
                    $effectId = 10;
                    $player->setHealth(20);
                     $this->showOnScreenAnimation($player, $effectId);
                     $player->sendPopup(
                        TextFormat::GREEN . "Your hearts restored!"
                    ); 
                  } else {
                    $player->sendMessage("u bot");
                  }
                    break;
                    
                    case 1:
                      if ($player instanceof Player) {
                    $effectId = 17;
                     $player->setFood($player->getMaxFood());
                     $this->showOnScreenAnimation($player, $effectId);
                     $player->sendPopup(
                        TextFormat::BLUE . "Your hunger bars restored!"
                    );
                      } else {
                        $player->sendMessage("u bot");
                      }
                      break;
            }
        });
        $form->setTitle("Shortcut Menu");
        $form->addButton("§4Heal");
        $form->addButton("§6Food");
        $form->sendToPlayer($player);
        return $form;
    }
}
