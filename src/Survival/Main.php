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
use pocketmine\level\sound\FizzSound;
use pocketmine\level\sound\AnvilUseSound;
use pocketmine\level\particle\HeartParticle;
use dktapps\pmforms\{CustomForm,CustomFormResponse,FormIcon,MenuForm,MenuOption,ModalForm};
use dktapps\pmforms\element\{Dropdown,Input,Label,Slider,StepSlider,Toggle};
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
                    $sender->sendForm(new MenuForm(
  "Shortcut Menu",
  "",
  [new MenuOption("§4Heal")],
  function(Player $sender): void {
    $effectId = 10;
                    $sender->setHealth(20);
                    $sender->sendPopup(
                        TextFormat::GREEN . "Your hearts restored!"
                    );
                    $this->showOnScreenAnimation($sender, $effectId);
                    $sender->getLevel()->addSound(new FizzSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    
                    $sender->getLevel()->addParticle(new HeartParticle(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
  },
  [new MenuOption("§1Repair")],
  function(Player $sender): void {
    $item = $sender->getInventory()->getItemInHand();
                    if ($item->getDamage() > 0) {
                        $item->setDamage(0);
                        $sender->getInventory()->setItemInHand($item);
                        $sender->sendPopup(
                            TextFormat::BLUE . "Item succesfully repaired!"
                        );
                        $this->showTotemEffect($sender);
                        $sender->getLevel()->addSound(new AnvilUseSound(new Vector3($sender->getX(), $sender->getY(), $sender->getZ())));
                    } else {
                        $sender->sendPopup(
                            TextFormat::RED . "Item does not have any damage!"
                        );
                    }
  }
));
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
    }

