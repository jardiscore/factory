<?php

declare(strict_types=1);

namespace JardisCore\Factory\Tests\unit;

use JardisCore\Factory\Factory;
use JardisPsr\ClassVersion\ClassVersionInterface;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use stdClass;
use InvalidArgumentException;

class FactoryTest extends TestCase
{
    private ContainerInterface $containerMock;
    private ClassVersionInterface $classVersionMock;

    protected function setUp(): void
    {
        $this->containerMock = $this->createMock(ContainerInterface::class);
        $this->classVersionMock = $this->createMock(ClassVersionInterface::class);
    }

    public function testConstructorWithoutParameters(): void
    {
        $factory = new Factory();

        $this->assertInstanceOf(Factory::class, $factory);
    }

    public function testConstructorWithContainer(): void
    {
        $factory = new Factory($this->containerMock);

        $this->assertInstanceOf(Factory::class, $factory);
    }

    public function testConstructorWithContainerAndClassVersion(): void
    {
        $factory = new Factory($this->containerMock, $this->classVersionMock);

        $this->assertInstanceOf(Factory::class, $factory);
    }

    public function testGetInstanceFromContainer(): void
    {
        $className = stdClass::class;
        $mockInstance = new stdClass();

        $this->containerMock->method('has')->with($className)->willReturn(true);
        $this->containerMock->method('get')->with($className)->willReturn($mockInstance);

        $factory = new Factory($this->containerMock);

        $result = $factory->get($className);

        $this->assertSame($mockInstance, $result);
    }

    public function testGetCreatesInstanceWhenContainerDoesNotHaveClass(): void
    {
        $className = stdClass::class;

        $this->containerMock->method('has')->with($className)->willReturn(false);

        $factory = new Factory($this->containerMock);

        $result = $factory->get($className);

        $this->assertInstanceOf($className, $result);
    }

    public function testGetCreatesInstanceWithoutContainer(): void
    {
        $className = stdClass::class;

        $factory = new Factory();

        $result = $factory->get($className);

        $this->assertInstanceOf($className, $result);
    }

    public function testGetCreatesInstanceWithEmptyConstructor(): void
    {
        $className = stdClass::class;

        $factory = new Factory();

        $result = $factory->get($className);

        $this->assertInstanceOf($className, $result);
    }

    public function testGetHandlesClassVersionAsObject(): void
    {
        $className = stdClass::class;
        $mockInstance = new stdClass();

        $this->classVersionMock->method('__invoke')
            ->with($className, null)
            ->willReturn($mockInstance);

        $factory = new Factory(null, $this->classVersionMock);

        $result = $factory->get($className);

        $this->assertSame($mockInstance, $result);
    }

    public function testGetHandlesClassVersionAsString(): void
    {
        $className = 'SomeClass';
        $versionHandler = stdClass::class;
        $mockInstance = new stdClass();

        $this->classVersionMock->method('__invoke')
            ->with($className, 'v2')
            ->willReturn($versionHandler);

        $this->containerMock->method('has')->with($versionHandler)->willReturn(true);
        $this->containerMock->method('get')->with($versionHandler)->willReturn($mockInstance);

        $factory = new Factory($this->containerMock, $this->classVersionMock);

        $result = $factory->get($className, 'v2');

        $this->assertSame($mockInstance, $result);
    }

    public function testGetThrowsExceptionWhenClassNotFound(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class NonExistentClass not found!');

        $className = 'NonExistentClass';

        $factory = new Factory();

        $factory->get($className);
    }

    public function testGetThrowsExceptionWhenClassVersionReturnsNonExistentClass(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Class NonExistentVersionedClass not found!');

        $className = stdClass::class;
        $versionHandler = 'NonExistentVersionedClass';

        $this->classVersionMock->method('__invoke')
            ->with($className, null)
            ->willReturn($versionHandler);

        $factory = new Factory(null, $this->classVersionMock);

        $factory->get($className);
    }

    public function testGetWithParametersAsArray(): void
    {
        $factory = new Factory();

        $result = $factory->get(Factory::class, null, [$this->containerMock, $this->classVersionMock]);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testGetWithParametersAsVariadic(): void
    {
        $factory = new Factory();

        $result = $factory->get(Factory::class, null, $this->containerMock, $this->classVersionMock);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testGetWithEmptyParametersArray(): void
    {
        $factory = new Factory();

        $result = $factory->get(stdClass::class, null, []);

        $this->assertInstanceOf(stdClass::class, $result);
    }

    public function testGetCreatesInstanceFromContainerEvenWithParameters(): void
    {
        $className = stdClass::class;
        $mockInstance = new stdClass();

        $this->containerMock->method('has')->with($className)->willReturn(true);
        $this->containerMock->method('get')->with($className)->willReturn($mockInstance);

        $factory = new Factory($this->containerMock);

        $result = $factory->get($className, null, 'param1', 'param2');

        // Container-Instanz wird zurückgegeben, Parameter werden ignoriert
        $this->assertSame($mockInstance, $result);
    }

    public function testGetWithClassVersionParameter(): void
    {
        $className = stdClass::class;
        $classVersion = 'v2.0';
        $versionedClass = Factory::class;

        $this->classVersionMock->method('__invoke')
            ->with($className, $classVersion)
            ->willReturn($versionedClass);

        $factory = new Factory(null, $this->classVersionMock);

        $result = $factory->get($className, $classVersion);

        $this->assertInstanceOf($versionedClass, $result);
    }

    public function testGetUsesContainerBeforeReflection(): void
    {
        $className = Factory::class;
        $mockInstance = new Factory();

        $this->containerMock->expects($this->once())
            ->method('has')
            ->with($className)
            ->willReturn(true);

        $this->containerMock->expects($this->once())
            ->method('get')
            ->with($className)
            ->willReturn($mockInstance);

        $factory = new Factory($this->containerMock);

        $result = $factory->get($className, null, $this->containerMock);

        $this->assertSame($mockInstance, $result);
    }

    public function testGetWithoutContainerUsesReflection(): void
    {
        $className = Factory::class;

        $factory = new Factory();

        $result = $factory->get($className, null, $this->containerMock, $this->classVersionMock);

        $this->assertInstanceOf($className, $result);
        $this->assertNotSame($factory, $result);
    }

    public function testCreateInstanceWithNamedParameters(): void
    {
        $factory = new Factory();

        // Test mit assoziativen Array-Parametern
        $result = $factory->get(Factory::class, null, [
            'container' => $this->containerMock,
            'classVersion' => $this->classVersionMock
        ]);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testGetReturnsNullWhenClassVersionReturnsNull(): void
    {
        $className = stdClass::class;

        $this->classVersionMock->method('__invoke')
            ->with($className, null)
            ->willReturn(null);

        $factory = new Factory(null, $this->classVersionMock);

        $result = $factory->get($className);

        $this->assertNull($result);
    }

    public function testGetWithMultipleParameters(): void
    {
        $factory = new Factory();

        $param1 = $this->containerMock;
        $param2 = $this->classVersionMock;
        $param3 = 'test3';

        $result = $factory->get(Factory::class, null, $param1, $param2, $param3);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testGetPreservesParameterOrder(): void
    {
        $factory = new Factory();

        $container = $this->containerMock;
        $classVersion = $this->classVersionMock;

        $result = $factory->get(Factory::class, null, $container, $classVersion);

        $this->assertInstanceOf(Factory::class, $result);
    }

    public function testGetCreatesInstanceForClassWithoutConstructorDespiteParameters(): void
    {
        $factory = new Factory();

        $result = $factory->get(stdClass::class, null, 'someParameter');

        $this->assertInstanceOf(stdClass::class, $result);
    }
}
