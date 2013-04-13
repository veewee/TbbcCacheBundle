<?php

namespace Kitano\CacheBundle\Tests\Aop\Interceptor;


use CG\Proxy\MethodInvocation;
use Kitano\CacheBundle\Aop\Interceptor\CacheableInterceptor;
use Kitano\CacheBundle\Metadata\ClassMetadata;
use Kitano\CacheBundle\Metadata\MethodMetadata;
use Metadata\MetadataFactoryInterface;

class CacheableInterceptorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException \LogicException
     */
    public function testInvokeThrowsExceptionWhenNoCachesAreSet()
    {
        list($interceptor,,) = $this->getInterceptor();

        $this->getInvocation($interceptor)->proceed();
    }

    protected function getInvocation(CacheableInterceptor $interceptor, $method = 'findSomething', $arguments = array())
    {
        if ('findSomething' === $method && 0 === count($arguments)) {
            $arguments = array(new \stdClass(), new \stdClass());
        }
        $object = new CacheableService();

        return new MethodInvocation(new \ReflectionMethod($object, $method), $object, $arguments, array($interceptor));
    }

    protected function getInterceptor(MetadataFactoryInterface $metadataFactory = null)
    {
        if (null === $metadataFactory) {
            $metadataFactory = $this->getMock('Metadata\MetadataFactoryInterface');

            $metadata = new ClassMetadata('Kitano\CacheBundle\Tests\Aop\Interceptor\CacheableService');
            $metadata->methodMetadata['findSomething'] = new MethodMetadata('Kitano\CacheBundle\Tests\Aop\Interceptor\CacheableService', 'findSomething');

            $metadataFactory
                ->expects($this->once())
                ->method('getMetadataForClass')
                ->with($this->equalTo('Kitano\CacheBundle\Tests\Aop\Interceptor\CacheableService'))
                ->will($this->returnValue($metadata))
            ;
        }

        $cacheManager = $this->getMock('Kitano\CacheBundle\Cache\CacheManagerInterface');
        $keyGenerator = $this->getMock('Kitano\CacheBundle\Cache\KeyGenerator\KeyGeneratorInterface');

        return array(
            new CacheableInterceptor($metadataFactory, $cacheManager, $keyGenerator),
            $cacheManager,
            $keyGenerator,
        );
    }
}

class CacheableService {

    public function findSomething($foo)
    {
        return $foo;
    }
}