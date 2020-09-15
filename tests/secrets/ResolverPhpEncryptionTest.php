<?php
namespace tests\secrets;

use Dotenv\Dotenv;
use extas\components\secrets\resolvers\ResolverPhpEncryption;
use extas\components\secrets\Secret;
use extas\interfaces\samples\parameters\ISampleParameter;
use PHPUnit\Framework\TestCase;

/**
 * Class ResolverPhpEncryptionTest
 *
 * @package tests\secrets
 * @author jeyroik <jeyroik@gmail.com>
 */
class ResolverPhpEncryptionTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $env = Dotenv::create(getcwd() . '/tests/');
        $env->load();
    }

    public function testEncryptAndDecrypt()
    {
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class,
            Secret::FIELD__VALUE => 'test.value',
            Secret::FIELD__PARAMETERS => [
                ResolverPhpEncryption::PARAM__PASSWORD => [
                    ISampleParameter::FIELD__NAME => ResolverPhpEncryption::PARAM__PASSWORD,
                    ISampleParameter::FIELD__VALUE => 'test.password'
                ]
            ]
        ]);

        $encrypted = $secret->encrypt();
        $this->assertTrue($encrypted, 'Can not encrypt');
        $this->assertNotEquals(
            'test.value',
            $secret->getValue(),
            'Incorrect encrypting: ' . $secret->getValue()
        );
        $this->assertNotEmpty(
            $secret->getParameterValue(ResolverPhpEncryption::PARAM__KEY),
            'Missed key parameter'
        );
        $this->assertEmpty(
            $secret->getParameterValue(ResolverPhpEncryption::PARAM__PASSWORD),
            'Password is not erased'
        );

        $secret->setParameterValue(ResolverPhpEncryption::PARAM__PASSWORD, 'test.password');
        $decrypted = $secret->decrypt();
        $this->assertTrue($decrypted, 'can not decrypt');

        $this->assertEquals(
            'test.value',
            $secret->getValue(),
            'Incorrect decrypting, wrong value: ' . $secret->getValue()
        );

        $this->assertEmpty(
            $secret->getParameterValue(ResolverPhpEncryption::PARAM__PASSWORD),
            'Password is not erased'
        );
    }

    public function testMissedPassword()
    {
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class
        ]);

        $encrypted = $secret->encrypt();
        $this->assertFalse($encrypted, 'Encrypted without a password');
    }

    public function testMissedKey()
    {
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class,
            Secret::FIELD__VALUE => 'test.value',
            Secret::FIELD__PARAMETERS => [
                ResolverPhpEncryption::PARAM__PASSWORD => [
                    ISampleParameter::FIELD__NAME => ResolverPhpEncryption::PARAM__PASSWORD,
                    ISampleParameter::FIELD__VALUE => 'test.password'
                ]
            ]
        ]);

        $decrypted = $secret->decrypt();
        $this->assertFalse($decrypted, 'Decrypted without a key');
    }

    public function testDecryptFailed()
    {
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class,
            Secret::FIELD__VALUE => 'test.value',
            Secret::FIELD__PARAMETERS => [
                ResolverPhpEncryption::PARAM__PASSWORD => [
                    ISampleParameter::FIELD__NAME => ResolverPhpEncryption::PARAM__PASSWORD,
                    ISampleParameter::FIELD__VALUE => 'test.password'
                ],
                ResolverPhpEncryption::PARAM__KEY => [
                    ISampleParameter::FIELD__NAME => ResolverPhpEncryption::PARAM__KEY,
                    ISampleParameter::FIELD__VALUE => 'some.key'
                ]
            ]
        ]);

        $decrypted = $secret->decrypt();
        $this->assertFalse($decrypted, 'Decrypting worked with an incorrect value and key');
    }
}
