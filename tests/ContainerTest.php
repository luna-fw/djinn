<?php
declare(strict_types=1);

use Luna\Container\Container;
use Luna\Container\Tests\ConcreteWithArguments;
use Luna\Container\Tests\ConcreteWithContractDependency1;
use Luna\Container\Tests\ConcreteWithContractDependency2;
use Luna\Container\Tests\ConcreteWithDependency;
use Luna\Container\Tests\ConcreteWithDependency2;
use Luna\Container\Tests\ConcreteWithoutArguments;
use Luna\Container\Tests\ConcreteWithoutArguments2;
use Luna\Container\Tests\Contract;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    /**
     * @var Container
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new Container();
    }

    public function testConcreteClassResolutionWithoutBinding(): void
    {
        $this->assertInstanceOf(
            ConcreteWithoutArguments::class,
            $this->container->get(ConcreteWithoutArguments::class)
        );
    }

    public function testClosureResolution(): void
    {
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument1');
            }
        );

        /** @var ConcreteWithArguments $result */
        $result = $this->container->get(ConcreteWithArguments::class);

        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result
        );

        $this->assertEquals('my_argument1', $result->getAtt1());
    }

    public function testClosureResolutionWithSubDependency(): void
    {

        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument2');
            }
        );

        $this->container->bind(
            ConcreteWithDependency::class,
            function (Container $container) {
                return new ConcreteWithDependency($container->get(ConcreteWithArguments::class));
            }
        );

        /** @var ConcreteWithDependency $result */
        $result = $this->container->get(ConcreteWithDependency::class);

        $this->assertInstanceOf(
            ConcreteWithDependency::class,
            $result
        );

        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result->getDependency()
        );

        $this->assertEquals('my_argument2', $result->getDependency()->getAtt1());
    }

    public function testClosureResolutionWithRecursiveSubDependency(): void
    {
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument3');
            }
        );

        /** @var ConcreteWithDependency $result */
        $result = $this->container->get(ConcreteWithDependency::class);

        $this->assertInstanceOf(
            ConcreteWithDependency::class,
            $result
        );

        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result->getDependency()
        );

        $this->assertEquals('my_argument3', $result->getDependency()->getAtt1());

    }

    /**
     * @group failing
     */
    public function testInterfaceToImplementationResolution()
    {
        $this->container->bind(
            Contract::class,
            ConcreteWithoutArguments::class
        );

        $result = $this->container->get(Contract::class);

        $this->assertInstanceOf(
            Contract::class,
            $result
        );

        $this->assertInstanceOf(
            ConcreteWithoutArguments::class,
            $result
        );
    }

    /**
     * Checks if the binded dependency always returns a fresh instance of the defined class (not a singleton)
     */
    public function testEnsureThatWishGrantedByBindingIsFresh()
    {
        $this->container->bind(
            Contract::class,
            ConcreteWithoutArguments::class
        );

        /** @var ConcreteWithoutArguments $result1 */
        $result1 = $this->container->get(Contract::class);
        $result1->property = 5;

        /** @var ConcreteWithoutArguments $result2 */
        $result2 = $this->container->get(Contract::class);
        $result2->property = 7;

        $this->assertNotEquals(7, $result1->property);

    }

    public function testInterfaceToClosureResolution()
    {
        $this->container->bind(
            Contract::class,
            function () {
                return new ConcreteWithoutArguments();
            }
        );

        $result = $this->container->get(Contract::class);

        $this->assertInstanceOf(
            Contract::class,
            $result
        );

        $this->assertInstanceOf(
            ConcreteWithoutArguments::class,
            $result
        );
    }

    public function testInterfaceToImplementationRecursive()
    {
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument4');
            }
        );

        $this->container->bind(Contract::class, ConcreteWithDependency::class);

        /** @var ConcreteWithDependency $result */
        $result = $this->container->get(Contract::class);

        $this->assertInstanceOf(
            ConcreteWithDependency::class,
            $result
        );

        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result->getDependency()
        );

        $this->assertEquals('my_argument4', $result->getDependency()->getAtt1());
    }

    public function testEnsureThatWishGrantedBySingletonIsAlwaysTheSameInstance()
    {
        $this->container->singleton(
            Contract::class,
            ConcreteWithoutArguments::class
        );

        /** @var ConcreteWithoutArguments $result1 */
        $result1 = $this->container->get(Contract::class);
        $result1->property = 2;

        /** @var ConcreteWithoutArguments $result2 */
        $result2 = $this->container->get(Contract::class);
        $result2->property = 3;

        $this->assertEquals(3, $result1->property);

    }

    public function testContextualBindingToClosure()
    {
        $this->container->contextual(
            ConcreteWithDependency::class,
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('value1');
            }
        );

        $this->container->contextual(
            ConcreteWithDependency2::class,
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('value2');
            }
        );

        /** @var ConcreteWithDependency $result1 */
        $result1 = $this->container->get(ConcreteWithDependency::class);
        /** @var ConcreteWithDependency2 $result2 */
        $result2 = $this->container->get(ConcreteWithDependency2::class);

        $this->assertInstanceOf(ConcreteWithDependency::class, $result1);
        $this->assertInstanceOf(ConcreteWithDependency2::class, $result2);
        $this->assertInstanceOf(ConcreteWithArguments::class, $result1->getDependency());
        $this->assertInstanceOf(ConcreteWithArguments::class, $result2->getDependency());
        $this->assertEquals('value1', $result1->getDependency()->getAtt1());
        $this->assertEquals('value2', $result2->getDependency()->getAtt1());
    }

    public function testContextualBindingToImplementation()
    {

        // when ConcreteWithContractDependency1 wants Contract
        $this->container->contextual(
            ConcreteWithContractDependency1::class,
            Contract::class,
            ConcreteWithoutArguments::class
        );

        // when anyone else wants Contract
        $this->container->bind(
            Contract::class,
            ConcreteWithoutArguments2::class
        );

        /** @var ConcreteWithContractDependency1 $result1 */
        $result1 = $this->container->get(ConcreteWithContractDependency1::class);
        /** @var ConcreteWithContractDependency2 $result2 */
        $result2 = $this->container->get(ConcreteWithContractDependency2::class);

        $this->assertInstanceOf(ConcreteWithContractDependency1::class, $result1);
        $this->assertInstanceOf(ConcreteWithContractDependency2::class, $result2);
        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result1->getDependency());
        $this->assertInstanceOf(ConcreteWithoutArguments2::class, $result2->getDependency());

    }

    /**
     * @expectedException \Luna\Container\Exceptions\UnresolvableContainerException
     * @expectedExceptionMessage Cannot resolve dependency 'Luna\Container\Tests\ConcreteWithArguments $dependency' for Luna\Container\Tests\ConcreteWithDependency
     */
    public function testImpossibleToResolve()
    {
        $this->container->get(ConcreteWithDependency::class);
    }

    /**
     * @expectedException \Luna\Container\Exceptions\BadBindingContainerException
     * @expectedExceptionMessage Can't resolve the class 'notaclass'. Check your binding.
     */
    public function testBadBinding()
    {
        $this->container->bind(Contract::class, 'notaclass');
        $this->container->get(Contract::class);
    }

    public function testContextualSingletonBindingToImplementation()
    {

        $this->container->contextualSingleton(
            ConcreteWithContractDependency1::class,
            Contract::class,
            ConcreteWithoutArguments::class
        );

        $this->container->bind(
            Contract::class,
            ConcreteWithoutArguments2::class
        );

        /** @var ConcreteWithContractDependency1 $result1 */
        $result1 = $this->container->get(ConcreteWithContractDependency1::class);
        /** @var ConcreteWithContractDependency2 $result2 */
        $result2 = $this->container->get(ConcreteWithContractDependency2::class);

        $this->assertInstanceOf(ConcreteWithContractDependency1::class, $result1);
        $this->assertInstanceOf(ConcreteWithContractDependency2::class, $result2);
        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result1->getDependency());
        $this->assertInstanceOf(ConcreteWithoutArguments2::class, $result2->getDependency());

        // ensuring the singleton
        /** @var ConcreteWithoutArguments $dependency1 */
        $dependency1 = $result1->getDependency();
        $dependency1->property = 1; // sets to 1

        /** @var ConcreteWithContractDependency1 $result3 */
        $result3 = $this->container->get(ConcreteWithContractDependency1::class); // resolve again
        /** @var ConcreteWithoutArguments $dependency2 */
        $dependency2 = $result3->getDependency();
        $dependency2->property = 2; // change to 2
        $this->assertEquals(2, $dependency1->property); // assert that the value changed (same object)

    }

    /**
     *
     */
    public function testContextualBindingByName()
    {
        // when ConcreteWithContractDependency1 wants $arg1
        $this->container->contextual(
            ConcreteWithContractDependency1::class,
            '$dependency',
            ConcreteWithoutArguments::class
        );

        /** @var ConcreteWithContractDependency1 $result */
        $result = $this->container->get(ConcreteWithContractDependency1::class);
        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result->getDependency());

    }

    /**
     *
     */
    public function testContextualSingletonBindingByName()
    {
        $this->container->contextualSingleton(
            ConcreteWithContractDependency1::class,
            '$dependency',
            ConcreteWithoutArguments::class
        );

        // checks the instances and the singleton
        /** @var ConcreteWithContractDependency1 $result */
        $result1 = $this->container->get(ConcreteWithContractDependency1::class);
        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result1->getDependency());
        $result1->getDependency()->property = 7;
        $result2 = $this->container->get(ConcreteWithContractDependency1::class);
        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result2->getDependency());
        $result2->getDependency()->property = 9;

        // result1 and result2 should be the same object, so the changes happens on booth
        $this->assertEquals(9, $result1->getDependency()->property);

    }

    /**
     *
     */
    public function testContextualBindingByNameToPrimitive()
    {
        $this->container->contextual(
            \Luna\Container\Tests\Controller::class,
            '$arg1',
            5
        );

        $this->container->contextual(
            \Luna\Container\Tests\Controller::class,
            '$arg2',
            'an awesome string'
        );

        $result = $this->container->get(\Luna\Container\Tests\Controller::class);

        $this->assertEquals(5, $result->getArg1());
        $this->assertEquals('an awesome string', $result->getArg2());

    }

    /**
     *
     */
    public function testRunMethod()
    {
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $class = \Luna\Container\Tests\Controller::class;
        $method = 'action1';
        $this->container->contextual("$class:$method", '$id', 567);

        $object = new \Luna\Container\Tests\Controller(1, 'a');
        $result = $this->container->run($method, $object);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(567, $result[1]);
    }

    /**
     *
     */
    public function testRunStaticMethod()
    {
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $class = \Luna\Container\Tests\Controller::class;
        $method = 'staticMethod';
        $this->container->contextual("$class:$method", '$arg', 876);

        $result = $this->container->run($method, $class);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(876, $result[1]);
    }

    /**
     *
     */
    public function testRunFunction()
    {
        require_once __DIR__ . '/app/function.php';
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $function = '\Luna\Container\Tests\func';
        $this->container->contextual($function, '$arg', 598);

        $result = $this->container->run($function);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(598, $result[1]);
    }

}
