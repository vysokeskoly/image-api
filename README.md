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

All routes has optional parameter `?namespace=...` which will be used as _sub directory_ in your storage path.
