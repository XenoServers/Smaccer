<?php
declare(strict_types=1);

namespace Xenophilicy\Smaccer;

use pocketmine\entity\Entity;
use Xenophilicy\Smaccer\entities\{SmaccerBat,
  SmaccerBlaze,
  SmaccerCaveSpider,
  SmaccerChicken,
  SmaccerCow,
  SmaccerCreeper,
  SmaccerDonkey,
  SmaccerElderGuardian,
  SmaccerEnderman,
  SmaccerEndermite,
  SmaccerEvoker,
  SmaccerGhast,
  SmaccerGuardian,
  SmaccerHorse,
  SmaccerHuman,
  SmaccerHusk,
  SmaccerIronGolem,
  SmaccerLavaSlime,
  SmaccerLlama,
  SmaccerMule,
  SmaccerMushroomCow,
  SmaccerOcelot,
  SmaccerPig,
  SmaccerPigZombie,
  SmaccerPolarBear,
  SmaccerRabbit,
  SmaccerSheep,
  SmaccerShulker,
  SmaccerSilverfish,
  SmaccerSkeleton,
  SmaccerSkeletonHorse,
  SmaccerSlime,
  SmaccerSnowman,
  SmaccerSpider,
  SmaccerSquid,
  SmaccerStray,
  SmaccerVex,
  SmaccerVillager,
  SmaccerVindicator,
  SmaccerWitch,
  SmaccerWither,
  SmaccerWitherSkeleton,
  SmaccerWolf,
  SmaccerZombie,
  SmaccerZombieHorse,
  SmaccerZombieVillager};
use Xenophilicy\Smaccer\entities\other\{SmaccerBoat, SmaccerFallingSand, SmaccerMinecart, SmaccerPrimedTNT};

/**
 * Class EntityManager
 * @package Xenophilicy\BaseSmaccer
 */
class EntityManager {
    const ENTITY_TYPES = ["Chicken", "Pig", "Sheep", "Cow", "MushroomCow", "Wolf", "Enderman", "Spider", "Skeleton", "PigZombie", "Creeper", "Slime", "Silverfish", "Villager", "Zombie", "Human", "Bat", "CaveSpider", "LavaSlime", "Ghast", "Ocelot", "Blaze", "ZombieVillager", "Snowman", "Minecart", "FallingSand", "Boat", "PrimedTNT", "Horse", "Donkey", "Mule", "SkeletonHorse", "ZombieHorse", "Witch", "Rabbit", "Stray", "Husk", "WitherSkeleton", "IronGolem", "Snowman", "LavaSlime", "Squid", "ElderGuardian", "Endermite", "Evoker", "Guardian", "PolarBear", "Shulker", "Vex", "Vindicator", "Wither", "Llama"];
    
    const ENTITY_ALIASES = ["MagmaCube" => "LavaSlime", "ZombiePigman" => "PigZombie", "Mooshroom" => "MushroomCow", "Player" => "Human", "VillagerZombie" => "ZombieVillager", "SnowGolem" => "Snowman", "FallingBlock" => "FallingSand", "FakeBlock" => "FallingSand", "VillagerGolem" => "IronGolem", "EGuardian" => "ElderGuardian", "Emite" => "Endermite"];
    
    public static function init(){
        foreach([SmaccerCreeper::class, SmaccerBat::class, SmaccerSheep::class, SmaccerPigZombie::class, SmaccerGhast::class, SmaccerBlaze::class, SmaccerIronGolem::class, SmaccerSnowman::class, SmaccerOcelot::class, SmaccerZombieVillager::class, SmaccerHuman::class, SmaccerCow::class, SmaccerZombie::class, SmaccerSquid::class, SmaccerVillager::class, SmaccerSpider::class, SmaccerPig::class, SmaccerMushroomCow::class, SmaccerWolf::class, SmaccerLavaSlime::class, SmaccerSilverfish::class, SmaccerSkeleton::class, SmaccerSlime::class, SmaccerChicken::class, SmaccerEnderman::class, SmaccerCaveSpider::class, SmaccerBoat::class, SmaccerMinecart::class, SmaccerMule::class, SmaccerWitch::class, SmaccerPrimedTNT::class, SmaccerHorse::class, SmaccerDonkey::class, SmaccerSkeletonHorse::class, SmaccerZombieHorse::class, SmaccerRabbit::class, SmaccerStray::class, SmaccerHusk::class, SmaccerWitherSkeleton::class, SmaccerFallingSand::class, SmaccerElderGuardian::class, SmaccerEndermite::class, SmaccerEvoker::class, SmaccerGuardian::class, SmaccerLlama::class, SmaccerPolarBear::class, SmaccerShulker::class, SmaccerVex::class, SmaccerVindicator::class, SmaccerWither::class] as $className){
            Entity::registerEntity($className, true);
        }
    }
}