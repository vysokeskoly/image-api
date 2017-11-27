VysokeSkoly/image-api
=====================

[![Build Status](https://travis-ci.org/vysokeskoly/image-api.svg?branch=master)](https://travis-ci.org/vysokeskoly/image-api)
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


## Build deb package

### Before 1st run only
- make sure you are building deb on `Ubuntu`

```bash
./install-deb-dependencies.sh
```

### Build deb
```bash
./build-deb.sh
```
