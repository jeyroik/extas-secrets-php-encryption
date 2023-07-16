<?php
namespace extas\components\secrets\resolvers;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\KeyProtectedByPassword;
use extas\components\exceptions\MissedOrUnknown;
use extas\components\Item;
use extas\components\secrets\ESecretFlag;
use extas\interfaces\extensions\secrets\IExtensionSecretWithKey;
use extas\interfaces\extensions\secrets\IExtensionSecretWithPassword;
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
    /**
     * @param ISecret $secret
     * @param ESecretFlag $flag
     * @return bool
     * @throws MissedOrUnknown
     */
    public function __invoke(ISecret &$secret, ESecretFlag $flag): bool
    {
        return $flag->isEncrypt()
            ? $this->encrypt($secret)
            : $this->decrypt($secret);
    }

    /**
     * @param ISecret|IExtensionSecretWithKey $secret
     * @return bool
     * @throws MissedOrUnknown
     */
    protected function encrypt(ISecret &$secret): bool
    {
        $password = $this->getPassword($secret);
        $protectedKey = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);
        $protectedEncodedKey = $protectedKey->saveToAsciiSafeString();
        $secret->withKey($protectedEncodedKey);

        $currentKey = $protectedKey->unlockKey($password);
        $encryptedValue = Crypto::encrypt($secret->getValue(), $currentKey);

        $this->setSecretValue($secret, $encryptedValue);

        return true;
    }

    /**
     * @param ISecret|IExtensionSecretWithKey $secret
     * @return bool
     * @throws MissedOrUnknown
     */
    protected function decrypt(ISecret &$secret): bool
    {
        $password = $this->getPassword($secret);
        $key = $secret->getKey();

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
        $secret->setParamValue(IExtensionSecretWithPassword::PARAM__PASSWORD, '');
    }

    /**
     * @param ISecret|IExtensionSecretWithPassword $secret
     * @return string
     * @throws MissedOrUnknown
     */
    protected function getPassword(ISecret $secret): string
    {
        $password = $secret->getPassword();

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
