Selenium tests
==============

## Requirements
- PHP 8.1
- composer
- Docker

---
## Run tests

### Install
- only first time or after changes in composer
```bash
cd selenium-tests
composer install
```

### Run all tests against `DEV` environment
```bash
cd selenium-tests
bin/run-all-dev-tests.sh
```

### Run all **prod-safe** (_read-only_) tests against _given_ environment
```bash
cd selenium-tests
bin/run-prod-safe-tests.sh dev # or prod
```

---
## Start server
Start 4 `Chrome` browsers like this (**alias**: `seleniumDockerStart`):

```bash
bin/selenium-server-start
```
or
```bash
docker-compose -f ./vendor/lmc/steward-lmc/bin/selenium-docker.yml up -d chrome
docker-compose -f ./vendor/lmc/steward-lmc/bin/selenium-docker.yml scale chrome=4
```

## Start server in **DEBUG** (_debug mode is run with possibility of connecting via **VNC client**_)
Start 1 `Chrome` browser like this (**alias**: `seleniumDockerDebug`):
```bash
docker-compose -f ./vendor/lmc/steward-lmc/bin/selenium-docker.yml up -d chrome-debug
```


---
## Run Tests
### Against **Local** environment (**alias**: `seleniumRunTestLocal`)
```bash
./vendor/bin/steward run -v --no-proxy local chrome --pattern "$1.php"
```
or verbose (**alias**: `seleniumRunTestLocalVV`)
```bash
./vendor/bin/steward run -vv --no-proxy local chrome --pattern "$1.php"
```
or debug (**alias**: `seleniumRunTestLocalDebug`)
```bash
./vendor/bin/steward run -vvv --no-proxy local chrome --pattern "$1.php"
```

### Local verbose example of `TitlePageTest`
```bash
seleniumRunTestLocalVV TitlePageTest
```
OR
```bash
./vendor/bin/steward run -vv --no-proxy local chrome --pattern "TitlePageTest.php"
```

### Against environment (**alias**: `seleniumRunTest`)

#### Environments:
- dev
- prod

```bash
./vendor/bin/steward run -v "$1"chrome --pattern "$2.php"
```
or verbose (**alias**: `seleniumRunTestVV`)
```bash
./vendor/bin/steward run -vv "$1"chrome --pattern "$2.php"
```
or debug (**alias**: `seleniumRunTestDebug`)
```bash
./vendor/bin/steward run -vvv "$1"chrome --pattern "$2.php"
```

### `Dev` verbose example of `TitlePageTest`
```bash
seleniumRunTestVV dev TitlePageTest
```
OR
```bash
./vendor/bin/steward run -v dev chrome --pattern "TitlePageTest.php"
```


---
## Stop server in the end
Stop (**alias**: `seleniumDockerStop`)

```bash
bin/selenium-server-stop
```
or
```bash
docker-compose -f ./vendor/lmc/steward-lmc/bin/selenium-docker.yml stop
docker-compose -f ./vendor/lmc/steward-lmc/bin/selenium-docker.yml rm -f
```


---
## VNC client (_on MAC_)
- `cmd + space` -> `screen Sharing`
- user: `seluser@localhost:5900`
- password: `secret` 


---
## Additional
- [Aliases](https://github.com/MortalFlesh/git-files)
