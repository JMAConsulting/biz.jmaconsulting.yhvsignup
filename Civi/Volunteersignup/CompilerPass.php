<?php

namespace Civi\Volunteersignup;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

use CRM_Yhvsignup_ExtensionUtil as E;

class CompilerPass implements CompilerPassInterface {

  public function process(ContainerBuilder $container) {
    if ($container->hasDefinition('action_provider')) {
      $actionProviderDefinition = $container->getDefinition('action_provider');
      $actionProviderDefinition->addMethodCall('addAction', array('volunteerSignup', 'Civi\Volunteersignup\Actions\VolunteerSignup', E::ts('Activity: Volunteer Signup'), array()));
      $actionProviderDefinition->addMethodCall('addAction', array('getVolunteerSignup', 'Civi\Volunteersignup\Actions\GetVolunteerSignup', E::ts('Activity: Get Volunteer Signup'), array()));
    }
  }
}
