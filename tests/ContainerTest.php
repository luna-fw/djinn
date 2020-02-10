<?php
declare(strict_types=1);

use Luna\Djinn\Djinn;
use Luna\Djinn\Exceptions\NotFoundException;
use Luna\Djinn\Tests\ConcreteWithArguments;
use Luna\Djinn\Tests\ConcreteWithContractDependency1;
use Luna\Djinn\Tests\ConcreteWithContractDependency2;
use Luna\Djinn\Tests\ConcreteWithDependency;
use Luna\Djinn\Tests\ConcreteWithDependency2;
use Luna\Djinn\Tests\ConcreteWithoutArguments;
use Luna\Djinn\Tests\ConcreteWithoutArguments2;
use Luna\Djinn\Tests\Contract;
use Luna\Djinn\Tests\Controller;
use PHPUnit\Framework\TestCase;

final class ContainerTest extends TestCase
{
    /**
     * @var Djinn
     */
    protected $container;

    protected function setUp()
    {
        $this->container = new Djinn();
    }

    /**
     * @throws NotFoundException
     */
    public function testConcreteClassResolutionWithoutBinding(): void
    {

        // just use the class name as key, and get the class instance
        $this->assertInstanceOf(
            ConcreteWithoutArguments::class,
            $this->container->get(ConcreteWithoutArguments::class)
        );
    }

    /**
     * @throws NotFoundException
     */
    public function testClosureResolution(): void
    {

        // configure the container to resolve the class ConcreteWithArguments to the same class instance, with a given argument inside the closure.
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument1');
            }
        );

        /** @var ConcreteWithArguments $result */
        $result = $this->container->get(ConcreteWithArguments::class);

        // assert the instance type
        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result
        );

        // assert the injected argument
        $this->assertEquals('my_argument1', $result->getAtt1());
    }

    /**
     * @see testClosureResolution
     * @throws NotFoundException
     */
    public function testClosureResolutionWithSubDependency(): void
    {

        // first configure the container to resolve the ConcreteWithArguments class (like in testClosureResolution)
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument2');
            }
        );

        // then configure the container to resolve ConcreteWithDependency using ConcreteWithArguments as dependency
        $this->container->bind(
            ConcreteWithDependency::class,
            function (Djinn $container) {
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

    /**
     * @see testClosureResolution
     * @throws NotFoundException
     */
    public function testClosureResolutionWithRecursiveSubDependency(): void
    {

        // first configure the container to resolve the ConcreteWithArguments class (like in testClosureResolution)
        $this->container->bind(
            ConcreteWithArguments::class,
            function () {
                return new ConcreteWithArguments('my_argument3');
            }
        );

        // just try to get ConcreteWithDependency and let the container resolve everything by himself
        /** @var ConcreteWithDependency $result */
        $result = $this->container->get(ConcreteWithDependency::class);

        // assert the main instance type
        $this->assertInstanceOf(
            ConcreteWithDependency::class,
            $result
        );

        // assert the dependency type
        $this->assertInstanceOf(
            ConcreteWithArguments::class,
            $result->getDependency()
        );

        // assert the dependency attribute
        $this->assertEquals('my_argument3', $result->getDependency()->getAtt1());

    }

    /**
     * @throws NotFoundException
     */
    public function testInterfaceToImplementationResolution()
    {

        // bind an interface to a concrete class
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
     * @throws NotFoundException
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

    /**
     * @see testInterfaceToImplementationResolution
     * @throws NotFoundException
     */
    public function testInterfaceToClosureResolution()
    {

        // bind a closure that just return a concrete instance that implements the interface
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

    /**
     * @throws NotFoundException
     */
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

    /**
     * @throws NotFoundException
     */
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

    /**
     * @throws NotFoundException
     */
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

    /**
     * @throws NotFoundException
     */
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
     * @expectedException \Luna\Djinn\Exceptions\NotFoundException
     * @expectedExceptionMessage Can't resolve your wish 'Luna\Djinn\Tests\ConcreteWithArguments'. Check your binding.
     * @throws NotFoundException
     */
    public function testImpossibleToResolve()
    {
        $this->container->get(ConcreteWithDependency::class);
    }

    /**
     * @expectedException \Luna\Djinn\Exceptions\NotFoundException
     * @expectedExceptionMessage Can't resolve your wish 'notaclass'. Check your binding.
     */
    public function testBadBinding()
    {
        $this->container->bind(Contract::class, 'notaclass');
        $this->container->get(Contract::class);
    }

    /**
     * @throws NotFoundException
     */
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
     * @throws NotFoundException
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
     * @throws NotFoundException
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
     * @throws NotFoundException
     */
    public function testContextualBindingByNameToPrimitive()
    {
        $this->container->contextual(
            Controller::class,
            '$arg1',
            5
        );

        $this->container->contextual(
            Controller::class,
            '$arg2',
            'an awesome string'
        );

        $result = $this->container->get(Controller::class);

        $this->assertEquals(5, $result->getArg1());
        $this->assertEquals('an awesome string', $result->getArg2());

    }

    /**
     * @throws NotFoundException
     */
    public function testRunMethod()
    {
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $class = Controller::class;
        $method = 'action1';
        $this->container->contextual("$class:$method", '$id', 567);

        $object = new Controller(1, 'a');
        $result = $this->container->run($method, $object);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(567, $result[1]);
    }

    /**
     * @throws NotFoundException
     */
    public function testRunStaticMethod()
    {
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $class = Controller::class;
        $method = 'staticMethod';
        $this->container->contextual("$class:$method", '$arg', 876);

        $result = $this->container->run($method, $class);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(876, $result[1]);
    }

    /**
     * @throws NotFoundException
     */
    public function testRunFunction()
    {
        require_once __DIR__ . '/app/function.php';
        $this->container->bind(Contract::class, ConcreteWithoutArguments::class);
        $function = '\Luna\Djinn\Tests\func';
        $this->container->contextual($function, '$arg', 598);

        $result = $this->container->run($function);

        $this->assertInstanceOf(ConcreteWithoutArguments::class, $result[0]);
        $this->assertEquals(598, $result[1]);
    }

    /**
     * @throws NotFoundException
     */
    public function testPrimitiveBinding()
    {
        $this->container->contextual(ConcreteWithArguments::class, '$arg1', 3);

        /** @var ConcreteWithArguments $result */
        $result = $this->container->get(ConcreteWithArguments::class);

        $this->assertInstanceOf(ConcreteWithArguments::class, $result);
        $this->assertEquals(3, $result->getAtt1());
    }
}
