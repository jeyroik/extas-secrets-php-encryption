<?php
namespace extas\components\secrets\resolvers;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\KeyProtectedByPassword;
use extas\components\exceptions\MissedOrUnknown;
use extas\components\Item;
use extas\interfaces\secrets\ISecret;
use extas\interfaces\secrets\ISecretResolver;

/**
 * Class ResolverPhpEncryption
 *
 * @package extas\components\secrets\resolvers
 * @author jeyroik <jeyroik@gmail.com>
 */
class ResolverPhpEncryption extends Item implements ISecretResolver
{
    public const PARAM__PASSWORD = 'password';
    public const PARAM__KEY = 'key';

    /**
     * @param ISecret $secret
     * @param string $flag
     * @return bool
     * @throws MissedOrUnknown
     */
    public function __invoke(ISecret &$secret, string $flag): bool
    {
        return $flag === $secret::FLAG__ENCRYPT
            ? $this->encrypt($secret)
            : $this->decrypt($secret);
    }

    /**
     * @param ISecret $secret
     * @return bool
     * @throws MissedOrUnknown
     */
    protected function encrypt(ISecret &$secret): bool
    {
        $password = $this->getPassword($secret);
        $protectedKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
        $protectedEncodedKey = $protectedKey->saveToAsciiSafeString();
        $secret->addParameterByValue(static::PARAM__KEY, $protectedEncodedKey);

        $currentKey = $protectedKey->unlockKey($password);
        $encryptedValue = Crypto::encrypt($secret->getValue(), $currentKey);

        $this->setSecretValue($secret, $encryptedValue);

        return true;
    }

    /**
     * @param ISecret $secret
     * @return bool
     * @throws MissedOrUnknown
     */
    protected function decrypt(ISecret &$secret): bool
    {
        $password = $this->getPassword($secret);
        $key = $secret->getParameterValue(static::PARAM__KEY, '');

        if (!$key) {
            throw new MissedOrUnknown('key parameter');
        }

        $protectedKey = KeyProtectedByPassword::loadFromAsciiSafeString($key);
        $currentKey = $protectedKey->unlockKey($password);
        $decryptedValue = Crypto::decrypt($secret->getValue(), $currentKey);

        $this->setSecretValue($secret, $decryptedValue);

        return true;
    }

    /**
     * @param ISecret $secret
     * @param string $value
     */
    protected function setSecretValue(ISecret &$secret, string $value)
    {
        $secret->setValue($value);
        $secret->setParameterValue(static::PARAM__PASSWORD, null);
    }

    /**
     * @param ISecret $secret
     * @return string
     * @throws MissedOrUnknown
     */
    protected function getPassword(ISecret $secret): string
    {
        $password = $secret->getParameterValue(static::PARAM__PASSWORD, '');

        if (!$password) {
            throw new MissedOrUnknown('password parameter');
        }

        return $password;
    }

    /**
     * @return string
     */
    protected function getSubjectForExtension(): string
    {
        return 'extas.secret.resolver.php.encryption';
    }
}
