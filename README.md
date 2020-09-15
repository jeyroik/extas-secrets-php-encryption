![tests](https://github.com/jeyroik/extas-secrets-php-encryption/workflows/PHP%20Composer/badge.svg?branch=master&event=push)
![codecov.io](https://codecov.io/gh/jeyroik/extas-secrets-php-encryption/coverage.svg?branch=master)
<a href="https://github.com/phpstan/phpstan"><img src="https://img.shields.io/badge/PHPStan-enabled-brightgreen.svg?style=flat" alt="PHPStan Enabled"></a> 

[![Latest Stable Version](https://poser.pugx.org/jeyroik/extas-secrets-php-encryption/v)](//packagist.org/packages/jeyroik/extas-q-crawlers)
[![Total Downloads](https://poser.pugx.org/jeyroik/extas-secrets-php-encryption/downloads)](//packagist.org/packages/jeyroik/extas-q-crawlers)
[![Dependents](https://poser.pugx.org/jeyroik/extas-secrets-php-encryption/dependents)](//packagist.org/packages/jeyroik/extas-q-crawlers)

# extas-secrets-php-encryption

Using defuse/php-encryption library for secrets

# Using

## Encrypting

```php
use extas\components\secrets\Secret;
use extas\components\secrets\resolvers\ResolverPhpEncryption;
use extas\interfaces\samples\parameters\ISampleParameter;

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
$secret->encrypt();

// something like def502000c7a1f23cafa6548837c6f2189849cce56ef714a8bc588c601b5e4c6117a3276cc0f85769dbc7d53cb4d36de20e568553c104b6810750b94f03a844658034c613ebe40e64e633cd13b024e74878ae4156a33d51692ac416aeba3
echo $secret->getValue();

// something like def10000def502004fa55e368b67b4987b47264ab977bba7a74e6e9cb5ad8c942cc6f4dffbae6622becf1717f7d37987bc9900a6d4cde97cc1dad99bfc6355a52dc778563f42ce0e49009cf45b1abd26261641bf18601bbca1828d0c
//                62d0ec79fb5fbbe50c787c4177704e38417ce90ae7a166b7ac74e49b3befae54a25033403324e1fdd7491261bab3f3c688605aec1b77d550eebfec593c3498ba524e4304c980868bf8313e586a03b221e22714cbe82dcfcb2760551f
//                1f4a26e75c81d522ed03acc772c9be005f8dd7a1ebddd65e5100555a43d7f5f9f2111b1185ce01fd255d4a2b2353e6d85a55a5840287a1afcd1ab390144df35990ec4c62c5e4af16
echo $secret->getParameterValue(ResolverPhpEncryption::PARAM__KEY);
echo $secret->getParameterValue(ResolverPhpEncryption::PARAM__PASSWORD); // empty
```

As you see, password is erasing after encrypting, so you should pass it every time you want to decrypt a value.
You need a password for using a key, sou you can store a key in databse without worrying.

## Decrypting

```php
use extas\components\secrets\Secret;
use extas\components\secrets\resolvers\ResolverPhpEncryption;
use extas\interfaces\samples\parameters\ISampleParameter;

/**
 * @var Secret $secret
 * $secret = new Secret([
 *     Secret::FIELD__CLASS => ResolverPhpEncryption::class,
 *     Secret::FIELD__VALUE => 'def502000c7a1f23cafa6548837c6f2189849cce56ef714a8bc588c601b5e4c6117a3276cc0f85769dbc7d53cb4d36de20e568553c104b6810750b94f03a844658034c613ebe40e64e633cd13b024e74878ae4156a33d51692ac416aeba3',
 *     Secret::FIELD__PARAMETERS => [
 *         ResolverPhpEncryption::PARAM__PASSWORD => [
 *             ISampleParameter::FIELD__NAME => ResolverPhpEncryption::PARAM__PASSWORD,
 *             ISampleParameter::FIELD__VALUE => 'test.password'
 *         ]
 *     ]
 * ]);
 */
$secret->decrypt();
echo $secret->getValue(); // test.value
echo $secret->getParameterValue(ResolverPhpEncryption::PARAM__PASSWORD); // empty
```

As you see, password is erasing after decrypting too.