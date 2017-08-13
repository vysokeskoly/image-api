<?php

namespace VysokeSkoly\ImageApi;

use Symfony\Component\HttpKernel\Bundle\Bundle;
use VysokeSkoly\ImageApi\DependencyInjection\VysokeSkolyImageApiExtension;

class ImageApi extends Bundle
{
    public function getContainerExtension()
    {
        return new VysokeSkolyImageApiExtension();
    }
}
