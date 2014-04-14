<?php

namespace JasonRoman\Bundle\TwigExtensionBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * @author Jason Roman <j@jayroman.com>
 */
class JasonRomanTwigExtensionBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        parent::build($container);
    }
}
