<?php

use Rector\Config\RectorConfig;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\Php80\Rector\Class_\AnnotationToAttributeRector;
use Rector\Php80\ValueObject\AnnotationToAttribute;
use Rector\Symfony\Rector\BinaryOp\ResponseStatusCodeRector;
use Rector\Symfony\Rector\ClassMethod\MergeMethodAnnotationToRouteAnnotationRector;
use Rector\Symfony\Rector\ClassMethod\ReplaceSensioRouteAnnotationWithSymfonyRector;
use Rector\Symfony\Rector\MethodCall\OptionNameRector;
use Rector\Symfony\Set\SensiolabsSetList;
use Rector\Symfony\Set\SymfonySetList;
use Symfony\Component\Routing\Annotation\Route;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->rule(ReplaceSensioRouteAnnotationWithSymfonyRector::class);
    $rectorConfig->rule(MergeMethodAnnotationToRouteAnnotationRector::class);

    $rectorConfig->ruleWithConfiguration(
        AnnotationToAttributeRector::class,
        [new AnnotationToAttribute(Route::class)]
    );

    $rectorConfig->rule(OptionNameRector::class);
    $rectorConfig->rule(ResponseStatusCodeRector::class);

    $rectorConfig->import(DoctrineSetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(SymfonySetList::ANNOTATIONS_TO_ATTRIBUTES);
    $rectorConfig->import(SensiolabsSetList::FRAMEWORK_EXTRA_61);
};
