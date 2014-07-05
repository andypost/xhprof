<?php

namespace Drupal\xhprof\Compiler;

use Drupal\Core\StreamWrapper\PublicStream;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class StoragePass
 */
class StoragePass implements CompilerPassInterface {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerBuilder $container
   *
   * @throws \InvalidArgumentException
   */
  public function process(ContainerBuilder $container) {
    // configure the xhprof.xhprof service
    if (FALSE === $container->hasDefinition('xhprof.xhprof')) {
      return;
    }

    $definition = $container->getDefinition('xhprof.xhprof');

    foreach ($container->findTaggedServiceIds('xhprof_storage') as $id => $attributes) {
      $definition->addMethodCall('addStorage', array($id, new Reference($id)));
    }
  }
}
