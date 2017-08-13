VysokeSkoly/image-api
=====================

Api for storing images


## Todo
- saveAction (see `JobsAcademy\AdminBundle\Controller\ImageController`)
- delete (see `JobsAcademy\AdminBundle\Command\CleanSwiftCommand`)
- get (vysokeskoyl? - cdn)


### In JobsAcademy
- Create new implementation on `ImageClientInterface`
    - then remove `SwiftClient`
- remove dependency on `tech/swift` - `SwiftClientLMC`
- `ImageClientInterface` usage:
    - `Image`
        - `auth`
        - `getItem`
    - `CleanSwiftCommand`
        - `auth`
        - `listAllItems`
        - `deleteItem`
    - `Import`
        - only pass instance to `Image`
    - `SwiftImageUploader` (`ImageUploaderInterface`)
        - `auth` 
        - `saveString` 

### In VysokeSkoly
- check `imageBundle`
