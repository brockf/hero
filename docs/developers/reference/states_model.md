# States Model

Retrieve one or a list of states/provinces/countries in the Hero database.

## Initialization

```
$this->load->model('states_model');
```

## Method Reference

## `string GetStateByCode (string $state)`

Retrieve a single state's data by it's 2-letter code.

## `string GetStateByName (string $state)`

Retrieve a single state's data by it's full name.

## `array GetStates ()`

Retrieve an array of all states/provinces, alphabetically sorted.

Each state in the array has the keys:

* *id*
* *code* - 2-letter abbreviation
* *name*

## `array GetCountries ()`

Retrieve an array of all countries, alphabetically sorted.

Each country in the array has the keys:

* *id*
* *iso2* - The 2-letter ISO-2 standard abbreviation
* *name*
