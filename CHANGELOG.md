# [3.0.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/1.1.1...3.0.0) (2022-07-05)

* Added documentation: default API client params, exceptions description, logging and symfony yaml config examples
* Removed deprecated classes ([7b6f0e8f](https://gitlab.loc/sdk-login/login-sdk-php/commit/7b6f0e8f8655e134d56dfa028c162d8ed5453c40))

To migrate from previous version you need to remove usage of deprecated classes in your project.

# [1.1.1](https://gitlab.loc/sdk-login/login-sdk-php/compare/1.0.0...1.0.1) (2021-06-15)

* Actualized documentation

# [1.1.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/1.0.1...1.1.0) (2021-06-02)

* Fixed SOLID issues
* Obsolete classes marked as deprecated

# [1.0.1](https://gitlab.loc/sdk-login/login-sdk-php/compare/1.0.0...1.0.1) (2021-05-30)

* Changed allowed cache deserialization classes

# [1.0.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.6.0...1.0.0) (2021-05-05)

### Features

* Added local development environment via docker and make ([More info](https://gitlab.loc/sdk-login/login-sdk-php/-/blob/1.0.0/Makefile))
* Added cache provider
* Removed GRPC and Session API implementation

# [0.6.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.5.0...0.6.0) (2021-08-30)

### Features

* Add multiple project's mode ([61ba2bd](https://gitlab.loc/sdk-login/login-sdk-php/commit/61ba2bde7b03780590562d202adafe799bae952e))

# [0.5.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.4.0...0.5.0) (2021-08-12)


### Features

* **compability:** add compatibility layer for PHP 8 and Lcobucci\JWT 4.x ([c4aba33](https://gitlab.loc/sdk-login/login-sdk-php/commit/c4aba333651bfddc10eb0378ab4a338ab363d964))

# [0.4.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.3.1...0.4.0) (2021-08-04)


### Features

* allow ignore project_id in config ([f179065](https://gitlab.loc/sdk-login/login-sdk-php/commit/f179065aed7719bd13fe19307f0d8375803fd140))

## [0.3.1](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.3.0...0.3.1) (2021-07-28)


### Bug Fixes

* **docs:** fix docs, update dependencies (previous commits) ([b3b8e94](https://gitlab.loc/sdk-login/login-sdk-php/commit/b3b8e9419013adb73ac8a70067e2feb9b24fd5cf))

# [0.3.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/0.2.0...0.3.0) (2021-06-28)


### Features

* check invalidation of the token ([67a7bb5](https://gitlab.loc/sdk-login/login-sdk-php/commit/67a7bb5fea8d2a311d4acbeddfda28ec7eea12b1))

# [0.2.0](https://gitlab.loc/sdk-login/login-sdk-php/compare/v0.1.0...0.2.0) (2021-06-09)


### Features

* **exception:** throws ExpiredTokenException in case of expired token ([185e886](https://gitlab.loc/sdk-login/login-sdk-php/commit/185e886af67a794e70871a768ed84c07786b26c4))
