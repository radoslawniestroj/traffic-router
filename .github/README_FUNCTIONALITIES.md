# Funkcjonalności

Traffic Router udostępnia cztery główne strategie:

## Random

### EqualTrafficSplit
Równomiernie rozdziela płatności pomiędzy wszystkie bramki (każda ma taki sam udział).

- **Zastosowanie:** gdy wszystkie bramki są równoważne i mają takie same limity.
- **Implementacja:** używa `random_int()` do losowego wyboru jednej z bramek.

```php
use App\Service\TrafficSplit\Random\EqualTrafficSplit;
use App\Dto\Payment;

$split = new EqualTrafficSplit([$g1, $g2, $g3]);
$gateway = $split->handlePayment(new Payment(100, 'EUR'));
```

### WeightedTrafficSplit
Rozdziela płatności zgodnie z zadanymi wagami procentowymi (np. 70/20/10).

- **Zastosowanie:** gdy jedna bramka ma niższe koszty, wyższy SLA lub większy limit i chcemy kierować do niej więcej ruchu.
- **Implementacja:** używa algorytmu weighted random selection (roulette wheel).

```php
use App\Service\TrafficSplit\Random\WeightedTrafficSplit;
use App\Dto\Payment;

$split = new WeightedTrafficSplit([
    ['gateway' => $g1, 'weight' => 70],
    ['gateway' => $g2, 'weight' => 20],
    ['gateway' => $g3, 'weight' => 10],
]);
$gateway = $split->handlePayment(new Payment(50, 'USD'));
```

## RoundRobin

### EqualTrafficSplit
Deterministyczny round-robin - wybiera kolejną bramkę w kolejności cyklicznej.

- **Zastosowanie:** gdy chcemy absolutnie równy rozkład (np. load balancing przy małych wolumenach).
- **Implementacja:** przewidywalny i powtarzalny rozkład (np. 3 bramki → cykl g1, g2, g3, g1…).

```php
use App\Service\TrafficSplit\RoundRobin\EqualTrafficSplit;
use App\Dto\Payment;

$split = new RoundRobinTrafficSplit([$g1, $g2, $g3]);
$gateway = $split->handlePayment(new Payment(10, 'PLN'));
```

### WeightedTrafficSplit
Deterministyczny weighted round-robin – kolejność powtarzalna, ale uwzględniająca wagi.

- **Zastosowanie:** gdy potrzebujemy powtarzalności i precyzyjnego rozkładu.
- **Implementacja:** używa algorytmu weighted selection w oparciu o round-robin.

```php
use App\Service\TrafficSplit\RoundRobin\WeightedTrafficSplit;
use App\Dto\Payment;

$split = new WeightedRoundRobinTrafficSplit([
    ['gateway' => $g1, 'weight' => 3],
    ['gateway' => $g2, 'weight' => 1],
]);
$gateway = $split->handlePayment(new Payment(25, 'EUR'));
```

# Podsumowanie

| Strategia                           | Losowa / Deterministyczna | Zalety                                                  | Wady                                                          |
|-------------------------------------| ------------------------- | ------------------------------------------------------- | ------------------------------------------------------------- |
| **Random EqualTrafficSplit**        | Losowa                    | Prosta, szybka w implementacji                          | Może w krótkim okresie dać nierówny rozkład                   |
| **Random WeightedTrafficSplit**     | Losowa (wg wag)           | Łatwo implementować, "statystycznie" trafia w proporcje | Brak deterministyczności, możliwe odchylenia                  |
| **RoundRobin EqualTrafficSplit**    | Deterministyczna          | Idealnie równy rozkład w długim okresie, powtarzalność  | Nie obsługuje wag                                             |
| **RoundRobin WeightedTrafficSplit** | Deterministyczna (wg wag) | Powtarzalny, proporcjonalny rozkład                     | Większa pamięciożerność (sekwencja zawiera powtórzone bramki) |
