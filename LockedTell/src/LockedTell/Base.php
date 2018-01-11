<?php

namespace LockedTell;

use pocketmine\plugin\PluginBase;

use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerQuitEvent;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;
 
class Base extends PluginBase implements Listener{
	
    public $lock;
    
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }
    
    public function onCommand(CommandSender $sender, Command $command, $label, array $args){
        switch(strtolower($command->getName())){
               case "locktell":
                   if(isset($args[0]) and $this->getServer()->getPlayer($args[0]) !== null){
                      if(isset($this->lock[$sender->getName()])){
                         $sender->sendMessage("§cYou tell has already locked, type @exit on chat for unlock");
                         return false;
                      }
                      $player = $this->getServer()->getPlayer($args[0]);
                      if($player->getName() == $sender->getName()){
                         $sender->sendMessage("§cWhy you try lock with you?");
                         return false;
                      }
                      $this->lock[$sender->getName()] = $player;
                      $sender->sendMessage("§aYou tell has locked with : ". $player->getName() .", type @exit on chat for unlock");
                      $player->sendMessage("§aThe player ". $sender->getName() ." has locked tell with you");
                      return true;
                   }else{
                      $sender->sendMessage("§cThis player no is available");
                      return false;
                   }
               break;
        }
    }
    
    public function onChat(PlayerChatEvent $event){
        $player = $event->getPlayer();
        $message = explode(" ", $event->getMessage());
        if($event->isCancelled()){
           return false;
        }
        if(isset($this->lock[$player->getName()])){
           $event->setCancelled(true);
           $other = $this->lock[$player->getName()];
           if($other == null){
              $player->sendMessage("§cThe player who you has locked your tell no is online");
              unset($this->lock[$player->getName()]);
              return false;
           }
           if($message[0] == "@exit"){
              $player->sendMessage("§eYou have unlocked your tell with ". $other->getName());
              $other->sendMessage("§eThe player ". $player->getName() ." has unlocked tell with you");
              unset($this->lock[$player->getName()]);
              return true;
           }
           $this->getServer()->dispatchCommand($player, "tell ". $other->getName() ." ". $event->getMessage());
           return true;       
        }
    }
    
    public function onQuit(PlayerQuitEvent $event){
        $player = $event->getPlayer();
        foreach($this->getServer()->getOnlinePlayers() as $all){
                if(isset($this->lock[$all->getName()])){
                   $lockedPlayer = $this->lock[$all->getName()];
                   if($lockedPlayer !== null){
                      if($lockedPlayer->getName() == $player->getName()){
                         $all->sendMessage("§cThe player ". $player->getName() ." is offline and your tell has unlocked");
                         unset($this->lock[$all->getName()]);
                      }
                   }
                }
        }
        if(isset($this->lock[$player->getName()])){
           $lockedPlayer = $this->lock[$player->getName()];
           if($lockedPlayer !== null){
              $lockedPlayer->sendMessage("§cThe player ". $player->getName() ." is offline and has unlocked tell with you");
              unset($this->lock[$player->getName()]);
              return true;
           }
        }
    }
}