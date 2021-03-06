<?php

/**
 * This file is part of the ApiGen (http://apigen.org)
 *
 * For the full copyright and license information, please view
 * the file license.md that was distributed with this source code.
 */

namespace ApiGen\ElementReflection\Php;

use ApiGen;
use ApiGen\ElementReflection\Behaviors\ExtensionInterface;
use ApiGen\ElementReflection\Exception\RuntimeException;
use ApiGen\ElementReflection\Php\Factory\ClassReflectionFactoryInterface;
use ApiGen\ElementReflection\Php\Factory\ParameterReflectionFactoryInterface;
use ReflectionMethod;
use ReflectionParameter;


class MethodReflection implements InternalReflectionInterface, ExtensionInterface
{

	/**
	 * @var array
	 */
	private $parameters;

	/**
	 * @var ClassReflectionFactoryInterface
	 */
	private $classReflectionFactory;

	/**
	 * @var ParameterReflectionFactoryInterface
	 */
	private $parameterReflectionFactory;

	/**
	 * @var ReflectionMethod
	 */
	private $internalReflectionMethod;


	/**
	 * @param mixed $className
	 * @param string $methodName
	 * @param ClassReflectionFactoryInterface $classReflectionFactory
	 * @param ParameterReflectionFactoryInterface $parameterReflectionFactory
	 */
	public function __construct(
		$className,
		$methodName,
		ClassReflectionFactoryInterface $classReflectionFactory,
		ParameterReflectionFactoryInterface $parameterReflectionFactory
	) {
		$this->internalReflectionMethod = new ReflectionMethod($className, $methodName);
		$this->classReflectionFactory = $classReflectionFactory;
		$this->parameterReflectionFactory = $parameterReflectionFactory;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getName()
	{
		return $this->internalReflectionMethod->getName();
	}


	/**
	 * {@inheritdoc}
	 */
	public function getDeclaringClass()
	{
		return $this->classReflectionFactory->create($this->internalReflectionMethod->getDeclaringClass()->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameter($parameter)
	{
		$parameters = $this->getParameters();
		if (is_numeric($parameter)) {
			if ( ! isset($parameters[$parameter])) {
				throw new RuntimeException(sprintf('There is no parameter at position "%d".', $parameter));
			}
			return $parameters[$parameter];

		} else {
			foreach ($parameters as $reflection) {
				if ($reflection->getName() === $parameter) {
					return $reflection;
				}
			}
			throw new RuntimeException(sprintf('There is no parameter "%s".', $parameter));
		}
	}


	/**
	 * {@inheritdoc}
	 */
	public function getParameters()
	{
		if ($this->parameters === NULL) {
			$this->parameters = array_map(function (ReflectionParameter $parameter) {
				return $this->parameterReflectionFactory->create(
					$parameter->getDeclaringFunction()->getName(),
					$parameter->getName(),
					$parameter->getDeclaringClass()->getName()
				);
			}, $this->internalReflectionMethod->getParameters());
		}
		return $this->parameters;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getPrettyName()
	{
		return sprintf('%s::%s()', $this->getDeclaringClass()->getName(), $this->getName());
	}


	/**
	 * {@inheritdoc}
	 */
	public function isVariadic()
	{
		return PHP_VERSION_ID >= 50600 ? $this->internalReflectionMethod->isVariadic() : FALSE;
	}


	/**
	 * {@inheritdoc}
	 */
	public function getExtension()
	{
		return $this->internalReflectionMethod->getExtension();
	}

}
