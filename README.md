# Login SDK for PHP

This package is for Xsolla Login JWT validation.

Supported encryption algorithms:

- **RS256** (RSA Signature with [SHA-256](https://en.wikipedia.org/wiki/SHA-2)) is
  an [asymmetric algorithm](https://en.wikipedia.org/wiki/Public-key_cryptography), and it uses a public/private key
  pair: the identity provider has a private (secret) key used to generate the signature, and the consumer of the JWT
  gets a public key to validate the signature. Since the public key, as opposed to the private key, doesn't need to be
  kept secured, most identity providers make it easily available for consumers to obtain and use (usually through a
  metadata URL).
- **HS256** ([HMAC](https://en.wikipedia.org/wiki/HMAC) with SHA-256), on the other hand, involves a combination of a
  hashing function and one (secret) key that is shared between the two parties used to generate the hash that will serve
  as the signature. Since the same key is used both to generate the signature and to validate it, care must be taken to
  ensure that the key is not compromised.

> **NOTE:** This package only for internal use.

## Getting started

### Install

Add login sdk repository

```shell
composer config repositories.sdk-login composer https://gitlab.loc/api/v4/group/566/-/packages/composer/
```

Install package

```shell
composer require xsolla/login-sdk-php
```

### Configuration

```php
use GuzzleHttp\Client;
use JMS\Serializer\SerializerBuilder;
use Lcobucci\JWT\Parser;
use Lcobucci\JWT\Signer\Key\InMemory;
use Xsolla\LoginSdk\Api\LoginApi;
use Xsolla\LoginSdk\Helper\TokenHelper;
use Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider;
use Xsolla\LoginSdk\Validation\AlgorithmChainedTokenValidatorDecorator;
use Xsolla\LoginSdk\Validation\ChainTokenValidator;
use Xsolla\LoginSdk\Validation\ClaimNotEmptyTokenValidator;
use Xsolla\LoginSdk\Validation\CompositeTokenValidator;
use Xsolla\LoginSdk\Validation\ExpireTokenValidator;
use Xsolla\LoginSdk\Validation\HmacTokenValidator;
use Xsolla\LoginSdk\Validation\RsaTokenValidator;

$defaults = [];

// If it is necessary you may disable rate-limits for requests to Login API from your project
// `project_name` and `secret_key` provides by Login Team on demand
$defaults['headers']['X-LOGIN-<project_name>-ACCEPT'] = '<secret_key>';


$client = new Client([
    'base_uri' => 'https://test-login.xsolla.com',
    'verify' => false,
    'timeout' => 5,
    'defaults' => $defaults
]);

$serializer = SerializerBuilder::create()->addDefaultHandlers()->build();

$api = new LoginApi($client, $serializer);

$keyProvider = new LoginApiKeyProvider($api);
$rsaTokenValidator = new RsaTokenValidator($keyProvider);

$key = InMemory::plainText('test-key');
$hmacTokenValidator = new HmacTokenValidator($key);

$validator = new CompositeTokenValidator([
    new ClaimNotEmptyTokenValidator(TokenHelper::CLAIM_PROJECT_ID),
    new ChainTokenValidator([
        new AlgorithmChainedTokenValidatorDecorator('RS256', $rsaTokenValidator),
        new AlgorithmChainedTokenValidatorDecorator('HS256', $hmacTokenValidator),
    ], 'Incorrect token algorithm'),
    new ExpireTokenValidator(),
]);
```

## Usage

```php
$parser = new Parser();

$rawToken = 'raw-jwt-token-string';

try {
    $token = $parser->parse($rawToken);

    $validator->validate($token);

    // validation ok
} catch (\Throwable $e) {
    // validation failed
}
```

### Exceptions

Method `TokenValidatorInterface::validate` throws `TokenValidationException`.

It may be caused by:

- Token has been expired
- Token has no required claim
- Validation failed
    - Login API error occurred
    - Login API responses token is invalid

### Logging

Login SDK PHP does not have logging by default, but you can implement a decorator pattern for any of your needs.

For example,

```php
class LoginApiLoggerDecorator implements LoginApiInterface
{
    /** @var LoginApiInterface */
    private $decorated;
    
    /** @var \Psr\Log\LoggerInterface */
    private $logger;

    /**
     * @param LoginApiInterface $decorated
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        $decorated,
        $logger
    ) {
        $this->decorated = $decorated;
        $this->logger = $logger;
    }
    
    public function getValidationKeysForLoginProject($projectId)
    {
        try {
            $this->logger->info('Trying to get validation keys for login project', [
                'project_id' => $projectId
            ]);
            
            $keys = $this->decorated->getValidationKeysForLoginProject($projectId);
            
            $this->logger->info('Validation keys for login project received successfully', [
                'project_id' => $projectId,
                'keys' => $keys
            ]);

            return $keys;
        } catch (LoginApiException $exception) {
            $this->logger->info('Exception occurred on validation keys for login project request', [
                'project_id' => $projectId,
                'exception' => $exception
            ]);

            throw $exception;
        }
    }

    public function validateHS256Token($token)
    {
        //...
    }
}
```

### Symfony Config Example

```yaml
  xsolla.login_api.client:
      class: GuzzleHttp\Client
      arguments:
          config:
              base_uri: 'https://test-login.xsolla.com'
              verify: false
              timeout: 5
              defaults:
                  X-LOGIN-<project_name>-ACCEPT: '<secret_key>' // Optional

  xsolla.login_api.service:
      public: true
      class: Xsolla\LoginSdk\Api\LoginApi
      arguments:
          client: '@xsolla.login_api.client'
          serializer: '@jms_serializer'

  xsolla.login_api.key_provider:
      class: Xsolla\LoginSdk\KeyProvider\LoginApiKeyProvider
      arguments:
          api: '@xsolla.login_api.service'

  xsolla.token_validator.rsa:
      class: Xsolla\LoginSdk\Validation\RsaTokenValidator
      arguments:
          keyProvider: '@xsolla.login_api.key_provider'

  xsolla.token_validator.hmac:
      class: Xsolla\LoginSdk\Validation\HmacApiTokenValidator
      arguments:
          api: '@xsolla.login_api.service'

  xsolla.token_validator.xsolla_login_project_id_claim_exist:
      class: Xsolla\LoginSdk\Validation\ClaimNotEmptyTokenValidator
      arguments:
          claimName: 'xsolla_login_project_id'

  xsolla.token_validator.rsa.chained:
      class: Xsolla\LoginSdk\Validation\AlgorithmChainedTokenValidatorDecorator
      arguments:
          algorithm: 'RS256'
          validator: '@xsolla.token_validator.rsa'

  xsolla.token_validator.hmac.chained:
      class: Xsolla\LoginSdk\Validation\AlgorithmChainedTokenValidatorDecorator
      arguments:
          algorithm: 'HC256'
          validator: '@xsolla.token_validator.hmac'

  xsolla.token_validator.chain:
      class: Xsolla\LoginSdk\Validation\ChainTokenValidator
      arguments:
          validators:
              - '@xsolla.token_validator.rsa.chained'
              - '@xsolla.token_validator.hmac.chained'
          errorMessage: 'Incorrect token algorithm'

  xsolla.token_validator.expire:
      class: Xsolla\LoginSdk\Validation\ExpireTokenValidator

  # This service should be used in cases where token validation is required 
  xsolla.token_validator.service:
      class: Xsolla\LoginSdk\Validation\CompositeTokenValidator
      arguments:
          validators:
              - '@xsolla.token_validator.xsolla_login_project_id_claim_exist'
              - '@xsolla.token_validator.chain'
              - '@xsolla.token_validator.expire'
```

## Developing

Local development used Docker. See `/environment/docker` directory.

### Prepare

Copy `.env.dist` to `.env` at `/environment/docker` directory.

Setup php version in `.env`, e.g. `PHP_VERSION=7.1`.

Build environment for development.

```shell
make build
```

> It will build image and install dependencies.

### Testing

Run phpunit tests:

```shell
make test
```

### Code style

PHP CS Fixer configuration lies at `.php_cs.dist`.

Run fixer:

```shell
make quality
```

> It will apply code style fixes to `/src` and `/tests` directory files. Also, phpstan analyse will be run.

### Run development shell

Run and enter into the container shell:

```shell
make sh
```

## Contributing

Dependency `lcobucci/jwt` have huge diff between versions for php7 and php8.

Therefore, two versions (tags) have been made:

- `1.x.x`, `3.x.x` – for php ^7.1
- `2.x.x`, `4.x.x` – for php ^8.0

### PHP 7.x

Use `php_v7` branch for development for php 7.x.

### PHP 8.x

Use `php_v8` branch for development for php 8.x.

### Release

To release add tag of version. Pipeline will publish it automatically to a GitLab package registry.

> Please, use [semver](https://semver.org/).
