<?php
/**
 * Container.php
 *
 * Taken from Laravel.
 *
 * @package   easy-digital-downloads
 * @copyright Copyright (c) 2021, Easy Digital Downloads
 * @license   GPL2+
 */

namespace EDD\Container;

use ArrayAccess;
use Closure;
use Exception;
use EDD\Container\Exceptions\BindingResolutionException;
use InvalidArgumentException;
use LogicException;
use ReflectionClass;
use ReflectionException;
use ReflectionParameter;

class Container implements ArrayAccess {
	/**
	 * An array of the types that have been resolved.
	 *
	 * @var bool[]
	 */
	protected $resolved = [];

	/**
	 * The container's bindings.
	 *
	 * @var array[]
	 */
	protected $bindings = [];

	/**
	 * The container's method bindings.
	 *
	 * @var Closure[]
	 */
	protected $methodBindings = [];

	/**
	 * The container's shared instances.
	 *
	 * @var object[]
	 */
	protected $instances = [];

	/**
	 * The registered type aliases.
	 *
	 * @var string[]
	 */
	protected $aliases = [];

	/**
	 * The registered aliases keyed by the abstract name.
	 *
	 * @var array[]
	 */
	protected $abstractAliases = [];

	/**
	 * The extension closures for services.
	 *
	 * @var array[]
	 */
	protected $extenders = [];

	/**
	 * All of the registered tags.
	 *
	 * @var array[]
	 */
	protected $tags = [];

	/**
	 * The stack of concretions currently being built.
	 *
	 * @var array[]
	 */
	protected $buildStack = [];

	/**
	 * The parameter override stack.
	 *
	 * @var array[]
	 */
	protected $with = [];

	/**
	 * All of the registered rebound callbacks.
	 *
	 * @var array[]
	 */
	protected $reboundCallbacks = [];

	/**
	 * All of the global resolving callbacks.
	 *
	 * @var Closure[]
	 */
	protected $globalResolvingCallbacks = [];

	/**
	 * All of the global after resolving callbacks.
	 *
	 * @var Closure[]
	 */
	protected $globalAfterResolvingCallbacks = [];

	/**
	 * All of the resolving callbacks by class type.
	 *
	 * @var array[]
	 */
	protected $resolvingCallbacks = [];

	/**
	 * All of the after resolving callbacks by class type.
	 *
	 * @var array[]
	 */
	protected $afterResolvingCallbacks = [];

	/**
	 * Determine if the given abstract type has been bound.
	 *
	 * @param string $abstract
	 *
	 * @return bool
	 */
	public function bound( $abstract ) {
		return isset( $this->bindings[ $abstract ] ) ||
			isset( $this->instances[ $abstract ] ) ||
			$this->isAlias( $abstract );
	}

	/**
	 * Returns true if the container can return an entry for the given identifier.
	 * Returns false otherwise.
	 *
	 * `has($id)` returning true does not mean that `get($id)` will not throw an exception.
	 * It does however mean that `get($id)` will not throw a `NotFoundExceptionInterface`.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return bool
	 */
	public function has( $id ) {
		return $this->bound( $id );
	}

	/**
	 * Determine if the given abstract type has been resolved.
	 *
	 * @param string $abstract
	 *
	 * @return bool
	 */
	public function resolved( $abstract ) {
		if ( $this->isAlias( $abstract ) ) {
			$abstract = $this->getAlias( $abstract );
		}

		return isset( $this->resolved[ $abstract ] ) ||
			isset( $this->instances[ $abstract ] );
	}

	/**
	 * Determine if a given type is shared.
	 *
	 * @param string $abstract
	 *
	 * @return bool
	 */
	public function isShared( $abstract ) {
		return isset( $this->instances[ $abstract ] ) ||
			( isset( $this->bindings[ $abstract ]['shared'] ) &&
				$this->bindings[ $abstract ]['shared'] === true );
	}

	/**
	 * Determine if a given string is an alias.
	 *
	 * @param string $name
	 *
	 * @return bool
	 */
	public function isAlias( $name ) {
		return isset( $this->aliases[ $name ] );
	}

	/**
	 * Register a binding with the container.
	 *
	 * @param string              $abstract
	 * @param Closure|string|null $concrete
	 * @param bool                $shared
	 *
	 * @return void
	 */
	public function bind( $abstract, $concrete = null, $shared = false ) {
		$this->dropStaleInstances( $abstract );

		// If no concrete type was given, we will simply set the concrete type to the
		// abstract type. After that, the concrete type to be registered as shared
		// without being forced to state their classes in both of the parameters.
		if ( is_null( $concrete ) ) {
			$concrete = $abstract;
		}

		// If the factory is not a Closure, it means it is just a class name which is
		// bound into this container to the abstract type and we will just wrap it
		// up inside its own Closure to give us more convenience when extending.
		if ( ! $concrete instanceof Closure ) {
			$concrete = $this->getClosure( $abstract, $concrete );
		}

		$this->bindings[ $abstract ] = compact( 'concrete', 'shared' );

		// If the abstract type was already resolved in this container we'll fire the
		// rebound listener so that any objects which have already gotten resolved
		// can have their copy of the object updated via the listener callbacks.
		if ( $this->resolved( $abstract ) ) {
			$this->rebound( $abstract );
		}
	}

	/**
	 * Get the Closure to be used when building a type.
	 *
	 * @param string $abstract
	 * @param string $concrete
	 *
	 * @return Closure
	 */
	protected function getClosure( $abstract, $concrete ) {
		return function ( $container, $parameters = [] ) use ( $abstract, $concrete ) {
			if ( $abstract == $concrete ) {
				return $container->build( $concrete );
			}

			return $container->resolve(
				$concrete,
				$parameters,
				$raiseEvents = false
			);
		};
	}

	/**
	 * Determine if the container has a method binding.
	 *
	 * @param string $method
	 *
	 * @return bool
	 */
	public function hasMethodBinding( $method ) {
		return isset( $this->methodBindings[ $method ] );
	}

	/**
	 * Bind a callback to resolve with Container::call.
	 *
	 * @param array|string $method
	 * @param Closure      $callback
	 *
	 * @return void
	 */
	public function bindMethod( $method, $callback ) {
		$this->methodBindings[ $this->parseBindMethod( $method ) ] = $callback;
	}

	/**
	 * Get the method to be bound in class@method format.
	 *
	 * @param array|string $method
	 *
	 * @return string
	 */
	protected function parseBindMethod( $method ) {
		if ( is_array( $method ) ) {
			return $method[0] . '@' . $method[1];
		}

		return $method;
	}

	/**
	 * Get the method binding for the given method.
	 *
	 * @param string $method
	 * @param mixed  $instance
	 *
	 * @return mixed
	 */
	public function callMethodBinding( $method, $instance ) {
		return call_user_func( $this->methodBindings[ $method ], $instance, $this );
	}

	/**
	 * Register a binding if it hasn't already been registered.
	 *
	 * @param string              $abstract
	 * @param Closure|string|null $concrete
	 * @param bool                $shared
	 *
	 * @return void
	 */
	public function bindIf( $abstract, $concrete = null, $shared = false ) {
		if ( ! $this->bound( $abstract ) ) {
			$this->bind( $abstract, $concrete, $shared );
		}
	}

	/**
	 * Register a shared binding in the container.
	 *
	 * @param string              $abstract
	 * @param Closure|string|null $concrete
	 *
	 * @return void
	 */
	public function singleton( $abstract, $concrete = null ) {
		$this->bind( $abstract, $concrete, true );
	}

	/**
	 * Register a shared binding if it hasn't already been registered.
	 *
	 * @param string              $abstract
	 * @param Closure|string|null $concrete
	 *
	 * @return void
	 */
	public function singletonIf( $abstract, $concrete = null ) {
		if ( ! $this->bound( $abstract ) ) {
			$this->singleton( $abstract, $concrete );
		}
	}

	/**
	 * "Extend" an abstract type in the container.
	 *
	 * @param string  $abstract
	 * @param Closure $closure
	 *
	 * @return void
	 *
	 * @throws \InvalidArgumentException
	 */
	public function extend( $abstract, Closure $closure ) {
		$abstract = $this->getAlias( $abstract );

		if ( isset( $this->instances[ $abstract ] ) ) {
			$this->instances[ $abstract ] = $closure( $this->instances[ $abstract ], $this );

			$this->rebound( $abstract );
		} else {
			$this->extenders[ $abstract ][] = $closure;

			if ( $this->resolved( $abstract ) ) {
				$this->rebound( $abstract );
			}
		}
	}

	/**
	 * Register an existing instance as shared in the container.
	 *
	 * @param string $abstract
	 * @param mixed  $instance
	 *
	 * @return mixed
	 */
	public function instance( $abstract, $instance ) {
		$this->removeAbstractAlias( $abstract );

		$isBound = $this->bound( $abstract );

		unset( $this->aliases[ $abstract ] );

		// We'll check to determine if this type has been bound before, and if it has
		// we will fire the rebound callbacks registered with the container and it
		// can be updated with consuming classes that have gotten resolved here.
		$this->instances[ $abstract ] = $instance;

		if ( $isBound ) {
			$this->rebound( $abstract );
		}

		return $instance;
	}

	/**
	 * Remove an alias from the contextual binding alias cache.
	 *
	 * @param string $searched
	 *
	 * @return void
	 */
	protected function removeAbstractAlias( $searched ) {
		if ( ! isset( $this->aliases[ $searched ] ) ) {
			return;
		}

		foreach ( $this->abstractAliases as $abstract => $aliases ) {
			foreach ( $aliases as $index => $alias ) {
				if ( $alias == $searched ) {
					unset( $this->abstractAliases[ $abstract ][ $index ] );
				}
			}
		}
	}

	/**
	 * Assign a set of tags to a given binding.
	 *
	 * @param array|string $abstracts
	 * @param array|mixed  ...$tags
	 *
	 * @return void
	 */
	public function tag( $abstracts, $tags ) {
		$tags = is_array( $tags ) ? $tags : array_slice( func_get_args(), 1 );

		foreach ( $tags as $tag ) {
			if ( ! isset( $this->tags[ $tag ] ) ) {
				$this->tags[ $tag ] = [];
			}

			foreach ( (array) $abstracts as $abstract ) {
				$this->tags[ $tag ][] = $abstract;
			}
		}
	}

	/**
	 * Alias a type to a different name.
	 *
	 * @param string $abstract
	 * @param string $alias
	 *
	 * @return void
	 *
	 * @throws LogicException
	 */
	public function alias( $abstract, $alias ) {
		if ( $alias === $abstract ) {
			throw new LogicException( "[{$abstract}] is aliased to itself." );
		}

		$this->aliases[ $alias ] = $abstract;

		$this->abstractAliases[ $abstract ][] = $alias;
	}

	/**
	 * Bind a new callback to an abstract's rebind event.
	 *
	 * @param string  $abstract
	 * @param Closure $callback
	 *
	 * @return mixed
	 */
	public function rebinding( $abstract, Closure $callback ) {
		$this->reboundCallbacks[ $abstract = $this->getAlias( $abstract ) ][] = $callback;

		if ( $this->bound( $abstract ) ) {
			return $this->make( $abstract );
		}

		return null;
	}

	/**
	 * Refresh an instance on the given target and method.
	 *
	 * @param string $abstract
	 * @param mixed  $target
	 * @param string $method
	 *
	 * @return mixed
	 */
	public function refresh( $abstract, $target, $method ) {
		return $this->rebinding(
			$abstract,
			function ( $app, $instance ) use ( $target, $method ) {
				$target->{$method}( $instance );
			}
		);
	}

	/**
	 * Fire the "rebound" callbacks for the given abstract type.
	 *
	 * @param string $abstract
	 *
	 * @return void
	 */
	protected function rebound( $abstract ) {
		$instance = $this->make( $abstract );

		foreach ( $this->getReboundCallbacks( $abstract ) as $callback ) {
			$callback( $this, $instance );
		}
	}

	/**
	 * Get the rebound callbacks for a given type.
	 *
	 * @param string $abstract
	 *
	 * @return array
	 */
	protected function getReboundCallbacks( $abstract ) {
		return isset( $this->reboundCallbacks[ $abstract ] ) ? $this->reboundCallbacks[ $abstract ] : [];
	}

	/**
	 * Get a closure to resolve the given type from the container.
	 *
	 * @param string $abstract
	 *
	 * @return Closure
	 */
	public function factory( $abstract ) {
		return function () use ( $abstract ) {
			return $this->make( $abstract );
		};
	}

	/**
	 * Resolve the given type from the container.
	 *
	 * @param string $abstract
	 * @param array  $parameters
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	public function make( $abstract, array $parameters = [] ) {
		return $this->resolve( $abstract, $parameters );
	}

	/**
	 * Finds an entry of the container by its identifier and returns it.
	 *
	 * @param string $id Identifier of the entry to look for.
	 *
	 * @return mixed Entry.
	 * @throws InvalidArgumentException|BindingResolutionException
	 */
	public function get( $id ) {
		try {
			return $this->resolve( $id );
		} catch ( Exception $e ) {
			if ( $this->has( $id ) ) {
				throw $e;
			}

			throw new InvalidArgumentException( $id, $e->getCode(), $e );
		}
	}

	/**
	 * Resolve the given type from the container.
	 *
	 * @param string $abstract
	 * @param array  $parameters
	 * @param bool   $raiseEvents
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 * @throws ReflectionException
	 */
	protected function resolve( $abstract, $parameters = [], $raiseEvents = true ) {
		$abstract = $this->getAlias( $abstract );

		$concrete = null;

		// If an instance of the type is currently being managed as a singleton we'll
		// just return an existing instance instead of instantiating new instances
		// so the developer can keep using the same objects instance every time.
		if ( isset( $this->instances[ $abstract ] ) ) {
			return $this->instances[ $abstract ];
		}

		$this->with[] = $parameters;

		if ( is_null( $concrete ) ) {
			$concrete = $this->getConcrete( $abstract );
		}

		// We're ready to instantiate an instance of the concrete type registered for
		// the binding. This will instantiate the types, as well as resolve any of
		// its "nested" dependencies recursively until all have gotten resolved.
		if ( $this->isBuildable( $concrete, $abstract ) ) {
			$object = $this->build( $concrete );
		} else {
			$object = $this->make( $concrete );
		}

		// If we defined any extenders for this type, we'll need to spin through them
		// and apply them to the object being built. This allows for the extension
		// of services, such as changing configuration or decorating the object.
		foreach ( $this->getExtenders( $abstract ) as $extender ) {
			$object = $extender( $object, $this );
		}

		// If the requested type is registered as a singleton we'll want to cache off
		// the instances in "memory" so we can return it later without creating an
		// entirely new instance of an object on each subsequent request for it.
		if ( $this->isShared( $abstract ) ) {
			$this->instances[ $abstract ] = $object;
		}

		if ( $raiseEvents ) {
			$this->fireResolvingCallbacks( $abstract, $object );
		}

		// Before returning, we will also set the resolved flag to "true" and pop off
		// the parameter overrides for this build. After those two things are done
		// we will be ready to return back the fully constructed class instance.
		$this->resolved[ $abstract ] = true;

		array_pop( $this->with );

		return $object;
	}

	/**
	 * Get the concrete type for a given abstract.
	 *
	 * @param string $abstract
	 *
	 * @return mixed
	 */
	protected function getConcrete( $abstract ) {
		// If we don't have a registered resolver or concrete for the type, we'll just
		// assume each type is a concrete name and will attempt to resolve it as is
		// since the container should be able to resolve concretes automatically.
		if ( isset( $this->bindings[ $abstract ] ) ) {
			return $this->bindings[ $abstract ]['concrete'];
		}

		return $abstract;
	}

	/**
	 * Determine if the given concrete is buildable.
	 *
	 * @param mixed  $concrete
	 * @param string $abstract
	 *
	 * @return bool
	 */
	protected function isBuildable( $concrete, $abstract ) {
		return $concrete === $abstract || $concrete instanceof Closure;
	}

	/**
	 * Instantiate a concrete instance of the given type.
	 *
	 * @param Closure|string $concrete
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 * @throws ReflectionException
	 */
	public function build( $concrete ) {
		// If the concrete type is actually a Closure, we will just execute it and
		// hand back the results of the functions, which allows functions to be
		// used as resolvers for more fine-tuned resolution of these objects.
		if ( $concrete instanceof Closure ) {
			return $concrete( $this, $this->getLastParameterOverride() );
		}

		try {
			$reflector = new ReflectionClass( $concrete );
		} catch ( ReflectionException $e ) {
			throw new InvalidArgumentException( "Target class [$concrete] does not exist.", 0, $e );
		}

		// If the type is not instantiable, the developer is attempting to resolve
		// an abstract type such as an Interface or Abstract Class and there is
		// no binding registered for the abstractions so we need to bail out.
		if ( ! $reflector->isInstantiable() ) {
			$this->notInstantiable( $concrete );
		}

		$this->buildStack[] = $concrete;

		$constructor = $reflector->getConstructor();

		// If there are no constructors, that means there are no dependencies then
		// we can just resolve the instances of the objects right away, without
		// resolving any other types or dependencies out of these containers.
		if ( is_null( $constructor ) ) {
			array_pop( $this->buildStack );

			return new $concrete;
		}

		$dependencies = $constructor->getParameters();

		// Once we have all the constructor's parameters we can create each of the
		// dependency instances and then use the reflection instances to make a
		// new instance of this class, injecting the created dependencies in.
		try {
			$instances = $this->resolveDependencies( $dependencies );
		} catch ( BindingResolutionException $e ) {
			array_pop( $this->buildStack );

			throw $e;
		}

		array_pop( $this->buildStack );

		return $reflector->newInstanceArgs( $instances );
	}

	/**
	 * Resolve all of the dependencies from the ReflectionParameters.
	 *
	 * @param array $dependencies
	 *
	 * @return array
	 * @throws BindingResolutionException
	 * @throws ReflectionException
	 */
	protected function resolveDependencies( array $dependencies ) {
		$results = [];

		foreach ( $dependencies as $dependency ) {
			// If this dependency has a override for this particular build we will use
			// that instead as the value. Otherwise, we will continue with this run
			// of resolutions and let reflection attempt to determine the result.
			if ( $this->hasParameterOverride( $dependency ) ) {
				$results[] = $this->getParameterOverride( $dependency );

				continue;
			}


			// @note: ReflectionParameter::getClass() is deprecated since PHP 8
			// @note: ReflectionParameter::getType() is not supported before PHP7
			$name = version_compare( PHP_VERSION, '8.0', '<' )
				? $dependency->getClass()
				: $dependency->getType()->getName();

			// If the class is null, it means the dependency is a string or some other
			// primitive type which we can not resolve since it is not a class and
			// we will just bomb out with an error since we have no-where to go.
			$result = is_null( $name )
				? $this->resolvePrimitive( $dependency )
				: $this->resolveClass( $dependency );

			if ( $dependency->isVariadic() ) {
				array_push( $results, ...$result );
			} else {
				$results[] = $result;
			}
		}

		return $results;
	}

	/**
	 * Determine if the given dependency has a parameter override.
	 *
	 * @param ReflectionParameter $dependency
	 *
	 * @return bool
	 */
	protected function hasParameterOverride( $dependency ) {
		return array_key_exists(
			$dependency->name,
			$this->getLastParameterOverride()
		);
	}

	/**
	 * Get a parameter override for a dependency.
	 *
	 * @param ReflectionParameter $dependency
	 *
	 * @return mixed
	 */
	protected function getParameterOverride( $dependency ) {
		return $this->getLastParameterOverride()[ $dependency->name ];
	}

	/**
	 * Get the last parameter override.
	 *
	 * @return array
	 */
	protected function getLastParameterOverride() {
		return count( $this->with ) ? end( $this->with ) : [];
	}

	/**
	 * Resolve a non-class hinted primitive dependency.
	 *
	 * @param ReflectionParameter $parameter
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	protected function resolvePrimitive( ReflectionParameter $parameter ) {
		if ( $parameter->isDefaultValueAvailable() ) {
			return $parameter->getDefaultValue();
		}

		$this->unresolvablePrimitive( $parameter );

		return null;
	}

	/**
	 * Resolve a class based dependency from the container.
	 *
	 * @param ReflectionParameter $parameter
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 * @throws ReflectionException
	 */
	protected function resolveClass( ReflectionParameter $parameter ) {
		try {
			// @note: ReflectionParameter::getClass() is deprecated since PHP 8
			// @note: ReflectionParameter::getType() is not supported before PHP7
			return version_compare( PHP_VERSION, '8.0', '<' )
				? $this->make( $parameter->getClass()->name )
				: $this->make( $parameter->getType()->getName() );
		}

			// If we can not resolve the class instance, we will check to see if the value
			// is optional, and if it is we will return the optional parameter value as
			// the value of the dependency, similarly to how we do this with scalars.
		catch ( BindingResolutionException $e ) {
			if ( $parameter->isDefaultValueAvailable() ) {
				return $parameter->getDefaultValue();
			}

			if ( $parameter->isVariadic() ) {
				return [];
			}

			throw $e;
		}
	}

	/**
	 * Throw an exception that the concrete is not instantiable.
	 *
	 * @param string $concrete
	 *
	 * @return void
	 * @throws BindingResolutionException
	 */
	protected function notInstantiable( $concrete ) {
		if ( ! empty( $this->buildStack ) ) {
			$previous = implode( ', ', $this->buildStack );

			$message = "Target [$concrete] is not instantiable while building [$previous].";
		} else {
			$message = "Target [$concrete] is not instantiable.";
		}

		throw new BindingResolutionException( $message );
	}

	/**
	 * Throw an exception for an unresolvable primitive.
	 *
	 * @param ReflectionParameter $parameter
	 *
	 * @return void
	 * @throws BindingResolutionException
	 */
	protected function unresolvablePrimitive( ReflectionParameter $parameter ) {
		$message = "Unresolvable dependency resolving [$parameter] in class {$parameter->getDeclaringClass()->getName()}";

		throw new BindingResolutionException( $message );
	}

	/**
	 * Register a new resolving callback.
	 *
	 * @param Closure|string $abstract
	 * @param Closure|null   $callback
	 *
	 * @return void
	 */
	public function resolving( $abstract, Closure $callback = null ) {
		if ( is_string( $abstract ) ) {
			$abstract = $this->getAlias( $abstract );
		}

		if ( is_null( $callback ) && $abstract instanceof Closure ) {
			$this->globalResolvingCallbacks[] = $abstract;
		} else {
			$this->resolvingCallbacks[ $abstract ][] = $callback;
		}
	}

	/**
	 * Register a new after resolving callback for all types.
	 *
	 * @param Closure|string $abstract
	 * @param Closure|null   $callback
	 *
	 * @return void
	 */
	public function afterResolving( $abstract, Closure $callback = null ) {
		if ( is_string( $abstract ) ) {
			$abstract = $this->getAlias( $abstract );
		}

		if ( $abstract instanceof Closure && is_null( $callback ) ) {
			$this->globalAfterResolvingCallbacks[] = $abstract;
		} else {
			$this->afterResolvingCallbacks[ $abstract ][] = $callback;
		}
	}

	/**
	 * Fire all of the resolving callbacks.
	 *
	 * @param string $abstract
	 * @param mixed  $object
	 *
	 * @return void
	 */
	protected function fireResolvingCallbacks( $abstract, $object ) {
		$this->fireCallbackArray( $object, $this->globalResolvingCallbacks );

		$this->fireCallbackArray(
			$object,
			$this->getCallbacksForType( $abstract, $object, $this->resolvingCallbacks )
		);

		$this->fireAfterResolvingCallbacks( $abstract, $object );
	}

	/**
	 * Fire all of the after resolving callbacks.
	 *
	 * @param string $abstract
	 * @param mixed  $object
	 *
	 * @return void
	 */
	protected function fireAfterResolvingCallbacks( $abstract, $object ) {
		$this->fireCallbackArray( $object, $this->globalAfterResolvingCallbacks );

		$this->fireCallbackArray(
			$object,
			$this->getCallbacksForType( $abstract, $object, $this->afterResolvingCallbacks )
		);
	}

	/**
	 * Get all callbacks for a given type.
	 *
	 * @param string $abstract
	 * @param object $object
	 * @param array  $callbacksPerType
	 *
	 * @return array
	 */
	protected function getCallbacksForType( $abstract, $object, array $callbacksPerType ) {
		$results = [];

		foreach ( $callbacksPerType as $type => $callbacks ) {
			if ( $type === $abstract || $object instanceof $type ) {
				array_push( $results, ...$callbacks );
			}
		}

		return $results;
	}

	/**
	 * Fire an array of callbacks with an object.
	 *
	 * @param mixed $object
	 * @param array $callbacks
	 *
	 * @return void
	 */
	protected function fireCallbackArray( $object, array $callbacks ) {
		foreach ( $callbacks as $callback ) {
			$callback( $object, $this );
		}
	}

	/**
	 * Get the container's bindings.
	 *
	 * @return array
	 */
	public function getBindings() {
		return $this->bindings;
	}

	/**
	 * Get the alias for an abstract if available.
	 *
	 * @param string $abstract
	 *
	 * @return string
	 */
	public function getAlias( $abstract ) {
		if ( ! isset( $this->aliases[ $abstract ] ) ) {
			return $abstract;
		}

		return $this->getAlias( $this->aliases[ $abstract ] );
	}

	/**
	 * Get the extender callbacks for a given type.
	 *
	 * @param string $abstract
	 *
	 * @return array
	 */
	protected function getExtenders( $abstract ) {
		$abstract = $this->getAlias( $abstract );

		return isset( $this->extenders[ $abstract ] ) ? $this->extenders[ $abstract ] : [];
	}

	/**
	 * Remove all of the extender callbacks for a given type.
	 *
	 * @param string $abstract
	 *
	 * @return void
	 */
	public function forgetExtenders( $abstract ) {
		unset( $this->extenders[ $this->getAlias( $abstract ) ] );
	}

	/**
	 * Drop all of the stale instances and aliases.
	 *
	 * @param string $abstract
	 *
	 * @return void
	 */
	protected function dropStaleInstances( $abstract ) {
		unset( $this->instances[ $abstract ], $this->aliases[ $abstract ] );
	}

	/**
	 * Remove a resolved instance from the instance cache.
	 *
	 * @param string $abstract
	 *
	 * @return void
	 */
	public function forgetInstance( $abstract ) {
		unset( $this->instances[ $abstract ] );
	}

	/**
	 * Clear all of the instances from the container.
	 *
	 * @return void
	 */
	public function forgetInstances() {
		$this->instances = [];
	}

	/**
	 * Flush the container of all bindings and resolved instances.
	 *
	 * @return void
	 */
	public function flush() {
		$this->aliases         = [];
		$this->resolved        = [];
		$this->bindings        = [];
		$this->instances       = [];
		$this->abstractAliases = [];
	}

	/**
	 * Determine if a given offset exists.
	 *
	 * @param string $key
	 *
	 * @return bool
	 */
	public function offsetExists( $key ) {
		return $this->bound( $key );
	}

	/**
	 * Get the value at a given offset.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 * @throws BindingResolutionException
	 */
	public function offsetGet( $key ) {
		return $this->make( $key );
	}

	/**
	 * Set the value at a given offset.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function offsetSet( $key, $value ) {
		$this->bind(
			$key,
			$value instanceof Closure ? $value : function () use ( $value ) {
				return $value;
			}
		);
	}

	/**
	 * Unset the value at a given offset.
	 *
	 * @param string $key
	 *
	 * @return void
	 */
	public function offsetUnset( $key ) {
		unset( $this->bindings[ $key ], $this->instances[ $key ], $this->resolved[ $key ] );
	}

	/**
	 * Dynamically access container services.
	 *
	 * @param string $key
	 *
	 * @return mixed
	 */
	public function __get( $key ) {
		return $this[ $key ];
	}

	/**
	 * Dynamically set container services.
	 *
	 * @param string $key
	 * @param mixed  $value
	 *
	 * @return void
	 */
	public function __set( $key, $value ) {
		$this[ $key ] = $value;
	}

	/**
	 * Checks to see if the key exists.
	 *
	 * @param $key
	 *
	 * @return bool
	 */
	public function __isset( $key ) {
		return isset( $this[ $key ] );
	}
}
