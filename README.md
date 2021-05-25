VysokeSkoly/image-api
=====================

[![PHP - Checks](https://github.com/vysokeskoly/image-api/actions/workflows/php-checks.yaml/badge.svg)](https://github.com/vysokeskoly/image-api/actions/workflows/php-checks.yaml)
[![Coverage Status](https://coveralls.io/repos/github/vysokeskoly/image-api/badge.svg?branch=master)](https://coveralls.io/github/vysokeskoly/image-api?branch=master)

Api for storing images

## Actions:
| action    | path                  | method    |
| ---       | ---                   | ---       |
| Homepage  | `/`                   | ANY       |
| Auth      | `/auth`               | ANY       |
| Save      | `/image/`             | POST      |
| Get       | `/image/:fileName`    | GET       |
| Delete    | `/image/:fileName`    | DELETE    |
| List all  | `/list/`              | GET       |

All routes have optional parameter `?namespace=...` which will be used as _sub directory_ in your storage path.

## Run locally
```shell
symfony server:start
```

## Build deb package

```bash
bin/build-deb-app
```
