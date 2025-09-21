# API

Przetestowanie aplikacji jest możliwe za pomocą endpointu:

POST `{{domain}}/api/payments`
Dostępne strategie to: `random_equal`, `random_weight`, `robin_equal` and `robin_weight`.
```json
{
    "amount": 10,
    "currency": "EUR",
    "strategy": "weighted",
    "weights": [
        {"name":"gateway1","weight":75},
        {"name":"gateway2","weight":15},
        {"name":"gateway3","weight":10}
    ]
}
```
</br>odpowiedź
```json
{
    "selected": "gateway1",
    "gateway_load": 42
}
```
