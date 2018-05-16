<?php

/**
 * Annotations
 *
 * Allows the creation of custom annotations in PHP.
 *
 * @category  Library
 * @package   Annotations
 * @author    Axel Nana <ax.lnana@outlook.com>
 * @copyright 2011-2015 Rasmus Schultz <rasmus@mindplay.dk>, 2018 Aliens Group, Inc.
 * @license   LGPL <http://github.com/ElementaryFramework/Annotations/blob/master/LICENSE>
 * @version   0.0.1
 *
 *
 * This file was originally a part of the php-annotation framework.
 *
 * (c) Rasmus Schultz <rasmus@mindplay.dk>
 *
 * <https://github.com/mindplay-dk/php-annotations>
 */

namespace ElementaryFramework\Annotations;
use ElementaryFramework\Annotations\Exceptions\AnnotationException;

/**
 * Thin, static class with shortcut methods for inspection of annotations
 *
 * Using this static wrapper is optional - if your application uses a service container
 * or a dependency injection container, you most likely want to configure an instance
 * of the AnnotationManager using that layer instead.
 */
abstract class Annotations
{
    /**
     * @var array Configuration for any public property of AnnotationManager.
     */
    private static $_config;

    /**
     * @var AnnotationManager Singleton AnnotationManager instance
     */
    private static $_manager;

    /**
     * Sets a config value.
     *
     * @param string $key   The config key.
     * @param mixed  $value The config value.
     *
     * @throws AnnotationException
     */
    public static function setConfig(string $key, $value)
    {
        switch ($key) {
            case "autoload":
            case "cache":
            case "suffix":
            case "namespace":
            case "debug":
                self::$_config[$key] = $value;
                break;

            default:
                throw new AnnotationException("Invalid config key.");
        }
    }

    /**
     * @return AnnotationManager a singleton instance
     */
    public static function getManager()
    {
        if (!isset(self::$_manager)) {
            self::$_manager = new AnnotationManager;
        }

        if (\is_array(self::$_config)) {
            foreach (self::$_config as $key => $value) {
                self::$_manager->$key = $value;
            }
        }

        return self::$_manager;
    }

    /**
     * Returns the UsageAnnotation for the annotation with the given class-name.
     *
     * @see AnnotationManager::getUsage()
     *
     * @param string $class
     *
     * @return UsageAnnotation
     *
     * @throws Exceptions\AnnotationException
     */
    public static function getUsage(string $class): UsageAnnotation
    {
        return self::getManager()->getUsage($class);
    }

    /**
     * Inspects class annotations
     *
     * @see AnnotationManager::getClassAnnotations()
     *
     * @param object $class The class name or instance.
     * @param string $type  The name of the annotation.
     *
     * @return IAnnotation[]
     *
     * @throws Exceptions\AnnotationException
     */
    public static function ofClass($class, string $type = null): array
    {
        return self::getManager()->getClassAnnotations($class, $type);
    }

    /**
     * Inspects method annotations
     *
     * @see AnnotationManager::getMethodAnnotations()
     *
     * @param object $class  The class name or instance.
     * @param string $method The name of the method.
     * @param string $type   The name of the annotation.
     *
     * @return IAnnotation[]
     *
     * @throws Exceptions\AnnotationException
     */
    public static function ofMethod($class, string $method = null, string $type = null): array
    {
        return self::getManager()->getMethodAnnotations($class, $method, $type);
    }

    /**
     * Inspects property Annotations
     *
     * @see AnnotationManager::getPropertyAnnotations()
     *
     * @param object $class    The class name or instance.
     * @param string $property The name of the property.
     * @param string $type     The name of the annotation.
     *
     * @return IAnnotation[]
     *
     * @throws Exceptions\AnnotationException
     */
    public static function ofProperty($class, string $property = null, string $type = null): array
    {
        return self::getManager()->getPropertyAnnotations($class, $property, $type);
    }

    /**
     * Checks if a class has the given annotation.
     *
     * @param object $class The class name or instance.
     * @param string $type  The name of the annotation.
     *
     * @return bool
     *
     * @throws Exceptions\AnnotationException
     */
    public static function classHasAnnotation($class, string $type)
    {
        return count(self::ofClass($class, $type)) > 0;
    }

    /**
     * Checks if a class method has the given annotation.
     *
     * @param object $class  The class name or instance.
     * @param string $method The name of the method.
     * @param string $type   The name of the annotation.
     *
     * @return bool
     *
     * @throws Exceptions\AnnotationException
     */
    public static function methodHasAnnotation($class, string $method, string $type)
    {
        return count(self::ofMethod($class, $method, $type)) > 0;
    }

    /**
     * Checks if a class property has the given annotation.
     *
     * @param object $class    The class name or instance.
     * @param string $property The name of the property.
     * @param string $type     The name of the annotation.
     *
     * @return bool
     *
     * @throws Exceptions\AnnotationException
     */
    public static function propertyHasAnnotation($class, string $property, string $type)
    {
        return count(self::ofProperty($class, $property, $type)) > 0;
    }
}
