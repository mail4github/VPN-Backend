<?php

declare(strict_types=1);

namespace App\Tests\Unit\Request;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Validator\Validator\ValidatorInterface;

abstract class MappedRequestTestCase extends KernelTestCase
{
    protected ValidatorInterface $validator;

    protected function setUp(): void
    {
        $container = static::getContainer();
        $validator = $container->get(ValidatorInterface::class);
        if ($validator instanceof ValidatorInterface) {
            $this->validator = $validator;
        }

        $userRepository = $this->getMockBuilder(UserRepository::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['findBy', 'findOneBy'])
            ->getMock();

        $userRepository->expects($this->any())
            ->method('findBy')
            ->willReturnMap([
                [['login' => 'short'], null, null, null, []],
                [['login' => 'tooLongLoginValue'], null, null, null, []],
                [['login' => 'validLength'], null, null, null, []],
                [['login' => 'duplicate'], null, null, null, [new User()]],
                [['login' => 'unique'], null, null, null, []],
                [['email' => 'test@email.org', 'isEmailVerified' => true], null, null, null, []],
                [['email' => 'test.test_9999@email.org', 'isEmailVerified' => true], null, null, null, []],
                [['email' => 'test+local@email.org', 'isEmailVerified' => true], null, null, null, []],
                [['email' => 'test.email.org', 'isEmailVerified' => true], null, null, null, []],
                [['email' => 'available@email.org', 'isEmailVerified' => true], null, null, null, []],
                [['email' => 'taken@email.org', 'isEmailVerified' => true], null, null, null, [new User()]],
            ]);

        $userRepository->expects($this->any())
            ->method('findOneBy')
            ->willReturnMap([
                [['email' => 'test@email.org'], null, new User()],
                [['email' => 'test.test_9999@email.org'], null, new User()],
                [['email' => 'test+local@email.org'], null, new User()],
                [['email' => 'test.email.org'], null, new User()],
                [['email' => 'unverified@email.org'], null, new User()],
                [['email' => 'verified@email.org'], null, (new User())->setIsEmailVerified()],
            ]);

        $container->set(UserRepository::class, $userRepository);
    }

    public function testRequiredPropertiesExist(): void
    {
        foreach ($this->requiredProperties() as $property) {
            $this->assertTrue(property_exists($this->requestClass(), $property));
        }
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function loginLengthOptionsProvider(): array
    {
        return [
            'less than 6 characters' => ['short', 1],
            'greater than 16 characters' => ['tooLongLoginValue', 1],
            'between 6 and 16 characters' => ['validLength', 0],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function loginUniqueOptionsProvider(): array
    {
        return [
            'unique login' => ['unique', 0],
            'duplicate login' => ['duplicate', 1],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function passwordLengthOptionsProvider(): array
    {
        return [
            'less than 8 characters' => ['Short', 1],
            'more than 8 characters' => ['longEnough123', 0],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function passwordRegexOptionsProvider(): array
    {
        return [
            'without uppercase letters' => ['longwithoutuppercase', 1],
            'with non-latin symbols' => ['UppercasewithКириллица', 1],
            'with forbidden symbols' => ['Symbols()+;', 1],
            'with allowed symbols' => ['Password!&allowed', 0],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function emailOptionsProvider(): array
    {
        return [
            'regular email' => ['test@email.org', 0],
            'email with symbols' => ['test.test_9999@email.org', 0],
            'alias email' => ['test+local@email.org', 0],
            'invalid email' => ['test.email.org', 1],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function emailAvailableOptionsProvider(): array
    {
        return [
            'available email' => ['available@email.org', 0],
            'taken email' => ['taken@email.org', 1],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function emailNotVerifiedOptionsProvider(): array
    {
        return [
            'verified email' => ['verified@email.org', 1],
            'unverified email' => ['unverified@email.org', 0],
        ];
    }

    /**
     * @return array<string, array<int, bool|int>>
     */
    public static function enableTwoFactorFlagOptionsProvider(): array
    {
        return [
            'true state' => [true, 0],
            'false state' => [false, 0],
        ];
    }

    /**
     * @return array<string, array<int, int|string>>
     */
    public static function verificationCodeOptionsProvider(): array
    {
        return [
            'less than 4 symbols' => ['123', 1],
            'more than 4 symbols' => ['12345', 1],
            'exactly 4 symbols' => ['1234', 0],
        ];
    }

    /**
     * @return array<string>
     */
    abstract protected function requiredProperties(): array;

    /**
     * @return class-string
     */
    abstract protected function requestClass(): string;
}
