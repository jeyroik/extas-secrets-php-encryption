<?php
namespace tests\secrets;

use extas\components\secrets\resolvers\ResolverPhpEncryption;
use extas\components\secrets\Secret;
use extas\interfaces\extensions\secrets\IExtensionSecretWithKey;
use extas\interfaces\extensions\secrets\IExtensionSecretWithPassword;
use extas\interfaces\parameters\IParam;
use extas\interfaces\secrets\ISecret;
use tests\ExtasTestCase;

/**
 * Class ResolverPhpEncryptionTest
 *
 * @package tests\secrets
 * @author jeyroik <jeyroik@gmail.com>
 */
class ResolverPhpEncryptionTest extends ExtasTestCase
{
    protected array $libsToInstall = [
        'jeyroik/extas-secrets' => ['php', 'php']
        //'vendor/lib' => ['php', 'json'] storage ext, extas ext
    ];
    protected bool $isNeedInstallLibsItems = true;
    protected string $testPath = __DIR__;

    public function testEncryptAndDecrypt()
    {
        /**
         * @var IExtensionSecretWithKey|IExtensionSecretWithPassword|ISecret $secret
         */
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class,
            Secret::FIELD__VALUE => 'test.value'
        ]);

        $encrypted = $secret->withPassword('test.password')->encrypt();
        $this->assertTrue($encrypted, 'Can not encrypt');
        $this->assertNotEquals(
            'test.value',
            $secret->getValue(),
            'Incorrect encrypting: ' . $secret->getValue()
        );
        $this->assertNotEmpty(
            $secret->getKey(),
            'Missed key parameter'
        );
        $this->assertEmpty(
            $secret->getPassword(),
            'Password is not erased'
        );

        $secret->setParamValue(IExtensionSecretWithPassword::PARAM__PASSWORD, 'test.password');
        $decrypted = $secret->decrypt();
        $this->assertTrue($decrypted, 'can not decrypt');

        $this->assertEquals(
            'test.value',
            $secret->getValue(),
            'Incorrect decrypting, wrong value: ' . $secret->getValue()
        );

        $this->assertEmpty(
            $secret->getPassword(),
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
            Secret::FIELD__VALUE => 'test.value'
        ]);

        $decrypted = $secret->withPassword('test.password')->decrypt();
        $this->assertFalse($decrypted, 'Decrypted without a key');
    }

    public function testDecryptFailed()
    {
        /**
         * @var IExtensionSecretWithKey|IExtensionSecretWithPassword|ISecret $secret
         */
        $secret = new Secret([
            Secret::FIELD__CLASS => ResolverPhpEncryption::class,
            Secret::FIELD__VALUE => 'test.value'
        ]);

        $secret->withPassword('test.password');
        $secret->withKey('some.key');
        $decrypted = $secret->decrypt();

        $this->assertFalse($decrypted, 'Decrypting worked with an incorrect value and key');
    }
}
