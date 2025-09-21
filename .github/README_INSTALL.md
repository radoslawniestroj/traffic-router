# Instalacja aplikacji

Instalacja projektu:
- uruchom komendę `docker-compose up -d`, pobiera i konfiguruje kontenery potrzebne do działania
- następnie `docker-compose exec php bash`, za jej pomocą dostajesz się do kontenera
- w kontenerze `composer install`, instaluje brakujący kod

I to już wszystko! Masz działający projekt traffic router. Sprawdź czy działa i odwiedź stronę [localhost](http://localhost:8080/).</br>
Polecam się teraz zapoznać z wypisanymi w spisie treści dostępnymi funkcjonalnościami.</br>
\* pamiętaj o tym, że niektóre przeglądarki jak np. Google Chrome blokują domeny http, w takim wypadku musisz zezwolić na działanie localhost w ustawieniach
