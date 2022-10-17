<?php

use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
use VysokeSkoly\ImageApi\Environment\VSEnv;
use VysokeSkoly\ImageApi\Environment\VysokeSkoly;
use VysokeSkoly\ImageApi\Kernel;
use VysokeSkoly\UtilsBundle\Service\DebugLevel;

require dirname(__DIR__).'/vendor/autoload.php';

// Prepare request parameters
$request = Request::createFromGlobals();

$debugLevel = new DebugLevel();
$vysokeSkolyApp = new VysokeSkoly(new VSEnv());
if ($vysokeSkolyApp->isDevEnvironment() || $vysokeSkolyApp->isInternalRequest($request)) {
    if ($request->query->has('dbg')) {
        $debugLevel->setLevel($request->query->getInt('dbg'));
        $vysokeSkolyApp->setDebugCookie($debugLevel);
    } else {
        $debugLevel->setLevel($request->cookies->getInt(VysokeSkoly::COOKIE_VYSOKE_SKOLY_DBG));
    }
}

// Determine configuration environment according to LMC environments
$symfonyEnvironment = $vysokeSkolyApp->getSymfonyEnvironment($debugLevel);

$kernel = new Kernel($symfonyEnvironment, $symfonyEnvironment === VysokeSkoly::SYMFONY_DEV_ENV);
$kernel->boot();

$container = $kernel->getContainer();

// switch on debug tools in debug mode
if ($debugLevel->isDebug()) {
    Debug::enable();
} elseif ($container->has('profiler')) {
    $container->get('profiler')->disable();
}

$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
