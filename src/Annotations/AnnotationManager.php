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
 * This class manages the retrieval of Annotations from source code files
 */
class AnnotationManager
{
    const CACHE_FORMAT_VERSION = 3;

    const MEMBER_CLASS = 'class';

    const MEMBER_PROPERTY = 'property';

    const MEMBER_METHOD = 'method';

    /**
     * @var boolean Enable PHP autoloader when searching for annotation classes (defaults to true)
     */
    public $autoload = true;

    /**
     * @var string The class-name suffix for Annotation classes.
     */
    public $suffix = 'Annotation';

    /**
     * @var string The default namespace for annotations with no namespace qualifier.
     */
    public $namespace = '';

    /**
     * @var AnnotationCache|bool a cache-provider used to store annotation-data after parsing; or false to disable caching
     * @see getAnnotationData()
     */
    public $cache;

    /**
     * @var array List of registered annotation aliases.
     */
    private $_registry = array(
        'api'            => false,
        'abstract'       => false,
        'access'         => false,
        'author'         => false,
        'category'       => false,
        'copyright'      => false,
        'deprecated'     => false,
        'example'        => false,
        'filesource'     => false,
        'final'          => false,
        'global'         => false,
        'ignore'         => false,
        'internal'       => false,
        'license'        => false,
        'link'           => false,
        'method'         => 'ElementaryFramework\Annotations\Standard\MethodAnnotation',
        'name'           => false,
        'package'        => false,
        'param'          => 'ElementaryFramework\Annotations\Standard\ParamAnnotation',
        'property'       => 'ElementaryFramework\Annotations\Standard\PropertyAnnotation',
        'property-read'  => 'ElementaryFramework\Annotations\Standard\PropertyReadAnnotation',
        'property-write' => 'ElementaryFramework\Annotations\Standard\PropertyWriteAnnotation',
        'return'         => 'ElementaryFramework\Annotations\Standard\ReturnAnnotation',
        'see'            => false,
        'since'          => false,
        'source'         => false,
        'static'         => false,
        'staticvar'      => false,
        'subpackage'     => false,
        'todo'           => false,
        'tutorial'       => false,
        'throws'         => false,
        'type'           => 'ElementaryFramework\Annotations\Standard\TypeAnnotation',
        'usage'          => 'ElementaryFramework\Annotations\UsageAnnotation',
        'stop'           => 'ElementaryFramework\Annotations\StopAnnotation',
        'uses'           => false,
        'var'            => 'ElementaryFramework\Annotations\Standard\VarAnnotation',
        'version'        => false,
    );

    /**
     * @var boolean $debug Set to TRUE to enable HTML output for debugging
     */
    public $debug = false;

    /**
     * @var AnnotationParser
     */
    protected $parser;

    /**
     * An internal cache for annotation-data loaded from source-code files
     *
     * @var AnnotationFile[] hash where absolute path to php source-file => AnnotationFile instance
     */
    protected $files = array();

    /**
     * @var array[] An internal cache for Annotation instances
     * @see getAnnotations()
     */
    protected $annotations = array();

    /**
     * @var bool[] An array of flags indicating which annotation sets have been initialized
     * @see getAnnotations()
     */
    protected $initialized = array();

    /**
     * @var UsageAnnotation[] An internal cache for UsageAnnotation instances
     */
    protected $usage = array();

    /**
     * @var UsageAnnotation The standard UsageAnnotation
     */
    private $_usageAnnotation;

    /**
     * @var string a seed for caching - used when generating cache keys, to prevent collisions
     * when using more than one AnnotationManager in the same application.
     */
    private $_cacheSeed = '';

    /**
     * Whether this version of PHP has support for traits.
     */
    private $_traitsSupported;

    /**
     * Initialize the Annotation Manager
     *
     * @param string $cacheSeed only needed if using more than one AnnotationManager in the same application
     */
    public function __construct(string $cacheSeed = '')
    {
        $this->_cacheSeed = $cacheSeed;
        $this->_usageAnnotation = new UsageAnnotation();
        $this->_usageAnnotation->class = true;
        $this->_usageAnnotation->inherited = true;
        $this->_traitsSupported = \version_compare(PHP_VERSION, '5.4.0', '>=');
    }

    /**
     * Creates and returns the AnnotationParser instance
     * @return AnnotationParser
     */
    public function getParser()
    {
        if (!isset($this->parser)) {
            $this->parser = new AnnotationParser($this);
            $this->parser->debug = $this->debug;
            $this->parser->autoload = $this->autoload;
        }

        return $this->parser;
    }

    /**
     * Retrieves annotation-data from a given source-code file.
     *
     * Member-names in the returned array have the following format: Class, Class::method or Class::$member
     *
     * @param string $path the path of the source-code file from which to obtain annotation-data.
     *
     * @return AnnotationFile
     *
     * @throws AnnotationException if cache is not configured
     *
     * @see $files
     * @see $cache
     */
    protected function getAnnotationFile(string $path): AnnotationFile
    {
        if (!isset($this->files[$path])) {
            if ($this->cache === null) {
                throw new AnnotationException("AnnotationManager::\$cache is not configured");
            }

            if ($this->cache === false) {
                # caching is disabled
                $code = $this->getParser()->parseFile($path);
                $data = eval($code);
            } else {
                $checksum = \crc32($path . ':' . $this->_cacheSeed . ':' . self::CACHE_FORMAT_VERSION);
                $key = \basename($path) . '-' . \sprintf('%x', $checksum);

                if (($this->cache->exists($key) === false) || (\filemtime($path) > $this->cache->getTimestamp($key))) {
                    $code = $this->getParser()->parseFile($path);
                    $this->cache->store($key, $code);
                }

                $data = $this->cache->fetch($key);
            }

            $this->files[$path] = new AnnotationFile($path, $data);
        }

        return $this->files[$path];
    }

    /**
     * Resolves a name, using built-in annotation name resolution rules, and the registry.
     *
     * @param string $name the annotation-name
     *
     * @return string|bool The fully qualified annotation class-name, or false if the
     * requested annotation has been disabled (set to false) in the registry.
     *
     * @see $_registry
     */
    public function resolveName(string $name)
    {
        if (\strpos($name, '\\') !== false) {
            return $name . $this->suffix; // annotation class-name is fully qualified
        }

        $type = \lcfirst($name);

        if (isset($this->_registry[$type])) {
            return $this->_registry[$type]; // type-name is registered
        }

        $type = \ucfirst(\strtr($name, '-', '_')) . $this->suffix;

        return \strlen($this->namespace)
            ? $this->namespace . '\\' . $type
            : $type;
    }

    /**
     * Constructs, initializes and returns IAnnotation objects
     *
     * @param string $class_name The name of the class from which to obtain Annotations
     * @param string $member_type The type of member, e.g. "class", "property" or "method"
     * @param string $member_name Optional member name, e.g. "method" or "$property"
     *
     * @return IAnnotation[] array of IAnnotation objects for the given class/member/name
     *
     * @throws AnnotationException for bad annotations
     */
    protected function getAnnotations(string $class_name, string $member_type = self::MEMBER_CLASS, string $member_name = null): array
    {
        $key = $class_name . ($member_name ? '::' . $member_name : '');

        if (!isset($this->initialized[$key])) {
            $annotations = array();
            $classAnnotations = array();

            if ($member_type !== self::MEMBER_CLASS) {
                $classAnnotations = $this->getAnnotations($class_name, self::MEMBER_CLASS);
            }

            $reflection = new \ReflectionClass($class_name);

            if ($reflection->getFileName() && !$reflection->isInternal()) {
                $file = $this->getAnnotationFile($reflection->getFileName());
            }

            $inherit = true; // inherit parent annotations unless directed not to

            if (isset($file)) {
                if (isset($file->data[$key])) {
                    foreach ($file->data[$key] as $spec) {
                        $name = $spec['#name']; // currently unused
                        $type = $spec['#type'];

                        unset($spec['#name'], $spec['#type']);

                        if (!\class_exists($type, $this->autoload)) {
                            throw new AnnotationException("Annotation type '{$type}' does not exist");
                        }

                        $annotation = new $type;

                        if (!($annotation instanceof IAnnotation)) {
                            throw new AnnotationException("Annotation type '{$type}' does not implement the mandatory IAnnotation interface");
                        }

                        if ($annotation instanceof IAnnotationFileAware) {
                            $annotation->setAnnotationFile($file);
                        }

                        $annotation->initAnnotation($spec);

                        $annotations[] = $annotation;
                    }

                    if ($member_type === self::MEMBER_CLASS) {
                        $classAnnotations = $annotations;
                    }
                } else if ($this->_traitsSupported && $member_name !== null) {
                    $traitAnnotations = array();

                    if (isset($file->traitMethodOverrides[$class_name][$member_name])) {
                        list($traitName, $originalMemberName) = $file->traitMethodOverrides[$class_name][$member_name];
                        $traitAnnotations = $this->getAnnotations($traitName, $member_type, $originalMemberName);
                    } else {
                        foreach ($reflection->getTraitNames() as $traitName) {
                            if ($this->classHasMember($traitName, $member_type, $member_name)) {
                                $traitAnnotations = $this->getAnnotations($traitName, $member_type, $member_name);
                                break;
                            }
                        }
                    }

                    $annotations = \array_merge($traitAnnotations, $annotations);
                }
            }

            foreach ($classAnnotations as $classAnnotation) {
                if ($classAnnotation instanceof StopAnnotation) {
                    $inherit = false; // do not inherit parent annotations
                }
            }

            if ($inherit && $parent = get_parent_class($class_name)) {
                $parent_annotations = array();

                if ($parent !== __NAMESPACE__ . '\Annotation') {
                    foreach ($this->getAnnotations($parent, $member_type, $member_name) as $annotation) {
                        if ($this->getUsage(\get_class($annotation))->inherited) {
                            $parent_annotations[] = $annotation;
                        }
                    }
                }

                $annotations = \array_merge($parent_annotations, $annotations);
            }

            $this->annotations[$key] = $this->applyConstraints($annotations, $member_type);

            $this->initialized[$key] = true;
        }

        return $this->annotations[$key];
    }

    /**
     * Determines whether a class or trait has the specified member.
     *
     * @param string $className The name of the class or trait to check
     * @param string $memberType The type of member, e.g. "property" or "method"
     * @param string $memberName The member name, e.g. "method" or "$property"
     *
     * @return bool whether class or trait has the specified member
     */
    protected function classHasMember(string $className, string $memberType, string $memberName): bool
    {
        if ($memberType === self::MEMBER_METHOD) {
            return \method_exists($className, $memberName);
        } else if ($memberType === self::MEMBER_PROPERTY) {
            return \property_exists($className, \ltrim($memberName, '$'));
        }
        return false;
    }

    /**
     * Validates the constraints (as defined by the UsageAnnotation of each annotation) of a
     * list of Annotations for a given type of member.
     *
     * @param IAnnotation[] $annotations An array of IAnnotation objects to be validated (sorted with inherited Annotations on top).
     * @param string        $member      The type of member to validate against (e.g. "class", "property" or "method").
     *
     * @return IAnnotation[] validated and filtered list of IAnnotations objects
     *
     * @throws AnnotationException if a constraint is violated.
     */
    protected function applyConstraints(array $annotations, string $member): array
    {
        $result = array();
        $annotationCount = \count($annotations);

        foreach ($annotations as $outerIndex => $annotation) {
            $type = \get_class($annotation);
            $usage = $this->getUsage($type);

            // Checks, that annotation can be applied to given class/method/property according to it's @usage annotation.
            if (!$usage->$member) {
                throw new AnnotationException("Annotation type '{$type}' cannot be applied to a {$member}");
            }

            if (!$usage->multiple) {
                // Process annotation coming after current (in the outer loop) and of same type.
                for ($innerIndex = $outerIndex + 1; $innerIndex < $annotationCount; $innerIndex += 1) {
                    if (!$annotations[$innerIndex] instanceof $type) {
                        continue;
                    }

                    if ($usage->inherited) {
                        continue 2; // Another annotation (in inner loop) overrides this one (in outer loop) - skip it.
                    }

                    throw new AnnotationException("Only one annotation of '{$type}' type may be applied to the same {$member}");
                }
            }

            $result[] = $annotation;
        }

        return $result;
    }

    /**
     * Filters annotations by class name
     *
     * @param IAnnotation[] $annotations An array of annotation objects
     * @param string $type The class-name by which to filter annotation objects; or annotation
     * type-name with a leading "@", e.g. "@var", which will be resolved through the registry
     *
     * @return array The filtered array of annotation objects - may return an empty array
     */
    protected function filterAnnotations(array $annotations, string $type): array
    {
        if (\substr($type, 0, 1) === '@') {
            $type = $this->resolveName(\substr($type, 1));
        }

        if ($type === false) {
            return array();
        }

        $result = array();

        foreach ($annotations as $annotation) {
            if ($annotation instanceof $type) {
                $result[] = $annotation;
            }
        }

        return $result;
    }

    /**
     * Obtain the UsageAnnotation for a given Annotation class
     *
     * @param string $class The Annotation type class-name
     *
     * @return UsageAnnotation
     *
     * @throws AnnotationException if the given class-name is invalid; if the annotation-type has no defined usage
     */
    public function getUsage(string $class): UsageAnnotation
    {
        if ($class === $this->_registry['usage']) {
            return $this->_usageAnnotation;
        }

        if (!isset($this->usage[$class])) {
            if (!\class_exists($class, $this->autoload)) {
                throw new AnnotationException("Annotation type '{$class}' does not exist");
            }

            $usage = $this->getAnnotations($class);

            if (\count($usage) === 0) {
                throw new AnnotationException("The class '{$class}' must have exactly one UsageAnnotation");
            } else {
                if (\count($usage) !== 1 || !($usage[0] instanceof UsageAnnotation)) {
                    throw new AnnotationException("The class '{$class}' must have exactly one UsageAnnotation (no other Annotations are allowed)");
                } else {
                    $usage = $usage[0];
                }
            }

            $this->usage[$class] = $usage;
        }

        return $this->usage[$class];
    }

    /**
     * Inspects Annotations applied to a given class
     *
     * @param string|object|\ReflectionClass $class A class name, an object, or a ReflectionClass instance
     * @param string $type An optional annotation class/interface name - if specified, only annotations of the given type are returned.
     *                     Alternatively, prefixing with "@" invokes name-resolution (allowing you to query by annotation name.)
     *
     * @return Annotation[] Annotation instances
     *
     * @throws AnnotationException if a given class-name is undefined
     */
    public function getClassAnnotations($class, string $type = null): array
    {
        if ($class instanceof \ReflectionClass) {
            $class = $class->getName();
        } elseif (\is_object($class)) {
            $class = \get_class($class);
        } else {
            $class = \ltrim($class, '\\');
        }

        if (!\class_exists($class, $this->autoload) &&
            !(\function_exists('trait_exists') && \trait_exists($class, $this->autoload))
        ) {
            if (\interface_exists($class, $this->autoload)) {
                throw new AnnotationException("Reading annotations from interface '{$class}' is not supported");
            }

            throw new AnnotationException("Unable to read annotations from an undefined class/trait '{$class}'");
        }

        if ($type === null) {
            return $this->getAnnotations($class);
        } else {
            return $this->filterAnnotations($this->getAnnotations($class), $type);
        }
    }

    /**
     * Inspects Annotations applied to a given method
     *
     * @param string|object|\ReflectionClass|\ReflectionMethod $class A class name, an object, a ReflectionClass, or a ReflectionMethod instance
     * @param string $method The name of a method of the given class (or null, if the first parameter is a ReflectionMethod)
     * @param string $type An optional annotation class/interface name - if specified, only annotations of the given type are returned.
     *                     Alternatively, prefixing with "@" invokes name-resolution (allowing you to query by annotation name.)
     *
     * @throws AnnotationException for undefined method or class-name
     *
     * @return IAnnotation[] list of Annotation objects
     */
    public function getMethodAnnotations($class, string $method = null, string $type = null): array
    {
        if ($class instanceof \ReflectionClass) {
            $class = $class->getName();
        } elseif ($class instanceof \ReflectionMethod) {
            $method = $class->name;
            $class = $class->class;
        } elseif (\is_object($class)) {
            $class = \get_class($class);
        } else {
            $class = \ltrim($class, '\\');
        }

        if (!\class_exists($class, $this->autoload)) {
            throw new AnnotationException("Unable to read annotations from an undefined class '{$class}'");
        }

        if (!\method_exists($class, $method)) {
            throw new AnnotationException("Unable to read annotations from an undefined method {$class}::{$method}()");
        }

        if ($type === null) {
            return $this->getAnnotations($class, self::MEMBER_METHOD, $method);
        } else {
            return $this->filterAnnotations($this->getAnnotations($class, self::MEMBER_METHOD, $method), $type);
        }
    }

    /**
     * Inspects Annotations applied to a given property
     *
     * @param string|object|\ReflectionClass|\ReflectionProperty $class A class name, an object, a ReflectionClass, or a ReflectionProperty instance
     * @param string $property The name of a defined property of the given class (or null, if the first parameter is a ReflectionProperty)
     * @param string $type An optional annotation class/interface name - if specified, only annotations of the given type are returned.
     *                     Alternatively, prefixing with "@" invokes name-resolution (allowing you to query by annotation name.)
     *
     * @return IAnnotation[] list of Annotation objects
     *
     * @throws AnnotationException for undefined class-name
     */
    public function getPropertyAnnotations($class, string $property = null, string $type = null): array
    {
        if ($class instanceof \ReflectionClass) {
            $class = $class->getName();
        } elseif ($class instanceof \ReflectionProperty) {
            $property = $class->name;
            $class = $class->class;
        } elseif (\is_object($class)) {
            $class = \get_class($class);
        } else {
            $class = \ltrim($class, '\\');
        }

        if (!\class_exists($class, $this->autoload)) {
            throw new AnnotationException("Unable to read annotations from an undefined class '{$class}'");
        }

        if (!\property_exists($class, $property)) {
            throw new AnnotationException("Unable to read annotations from an undefined property {$class}::\${$property}");
        }

        if ($type === null) {
            return $this->getAnnotations($class, self::MEMBER_PROPERTY, '$' . $property);
        } else {
            return $this->filterAnnotations($this->getAnnotations($class, self::MEMBER_PROPERTY, '$' . $property), $type);
        }
    }

    /**
     * Adds an annotation in the registry.
     *
     * @param string      $annotation The annotation to register.
     * @param string|bool $class      The full name of the class.
     */
    public function registerAnnotation(string $annotation, $class)
    {
        $this->_registry[$annotation] = $class;
    }
}
