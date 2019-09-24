## Description
This is a PHP App without any framework only using Eloquent library to handle DB connections and VueJs library to consume the API.

## API Documentation

1. To create a new Interval:
```
POST <domain>/priceInterval/insert
```
Payload format needs to be JSON, `date_start`, `date_end` and `price` fields are required.

```
{
	"date_start": "2019-09-01",
	"date_end": "2019-09-22",
	"price": 2
}
```

2. To update an Interval:
```
PUT <domain>/priceInterval/update
```
Payload format needs to be JSON, `date_start`, `date_end`, `price` and `id` fields are required.

```
{
	"date_start": "2019-09-21",
	"date_end": "2019-09-22",
	"price": 2,
	"id": 2
}
```
3. To delete an Interval:
```
DELETE <domain>/priceInterval/delete/<interval_id>
```
Download postman collection for this API from [here](https://drive.google.com/open?id=1LekvLEQzefVazmEo86pa32p4Z54yhpSP)
