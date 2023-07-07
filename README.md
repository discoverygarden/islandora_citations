## How to use citeproc-php ##
citeproc-php renders CSL JSON metadata into html formatted citations using a stylesheet which defines the 
citation rules. 
### Build a first simple script ###

```php
<?php
use Seboettg\CiteProc\StyleSheet; 
use Seboettg\CiteProc\CiteProc;

/**
 * Class CitationsService.
 */
class IslandoraCitationsService {

  public function renderFromMetadata($metadata, $style, $mode) {
    $stylesheet = StyleSheet::loadStyleSheet($style);
    $citeProc = new CiteProc($stylesheet);
    return $citeProc->render(json_decode($metadata), $mode);
  }

  public function render() {
    $metadata = '[
        {
            "author": [
                {
                    "family": "Doe",
                    "given": "James",
                    "suffix": "III"
                }
            ],
            "id": "item-1",
            "issued": {
                "date-parts": [
                    [
                        "2001"
                    ]
                ]
            },
            "title": "My Anonymous Heritage",
            "type": "book"
        },
        {
            "author": [
                {
                    "family": "Anderson",
                    "given": "John"
                },
                {
                    "family": "Brown",
                    "given": "John"
                }
            ],
            "id": "ITEM-2",
            "type": "book",
            "title": "Two authors writing a book"
        }
    ]';
    return renderFromMetadata($metadata, 'din-1505-2', 'citation');
  }
}
```
