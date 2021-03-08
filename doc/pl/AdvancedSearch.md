Zaawansowane przeszukiwanie katalogu
====================================

Zaawansowane wyszukiwanie w katalogu jest włączone w "Trybie eksperta" na stronie Ustawienia &gt; Dodatkowe funkcje.

Na stronie katalogu, w widżecie "Znajdź kanały" (zazwyczaj na pasku bocznym) widoczna jest opcja "Zaawansowane". Kliknięcie jej otwiera kolejne pole wyszukiwania umożliwiające wprowadzenie żądań wyszukiwania zaawansowanego.

Zaawansowane żądania zawierają:

* name=xxx 
[Nazwa kanału zawiera xxx]

* address=xxx
[Adres kanału (webbie) zawiera xxx]

* locale=xxx
[Lokalizaja (zazwyczaj 'city') zawiera xxx]

* region=xxx
[Region (stan/terytorium) zawiera xxx]

* postcode=xxx
[Kod pocztowy lub kod ZIP zawiera xxx]

* country=xxx
[Nazwa kraju zawiera xxx]

* gender=xxx
[Płeć zawiera xxx]

* marital=xxx
[Stan cywilny zawiera xxx]

* sexual=xxx
[Preferencje seksualne zawierają xxx]

* keywords=xxx
[Słowa kluczowe zawierają xxx]

Istnieje wiele powodów, dla których dopasowanie może nie zwrócić tego, czego szukasz, ponieważ wiele kanałów nie podaje szczegółowych informacji w swoim domyślnym (publicznym) profilu, a wiele z tych pól umożliwia wprowadzanie dowolnego tekstu w kilku językach - i sprawia to trudność w dokładnym dopasowaniu. Na przykład możesz uzyskać lepszy wynik, chcąc znaleźć kogoś w USA, nie za pomocą frazy `'country = u'` (bo pojawią sie też kanały z Niemiec, Bułgarii i Australii), a za pomocą fraz US, U.S.A, USA, United States, itd.

Przyszłe wersje tego narzędzia mogą już działać lepiej. 

Żądania można łączyć ze sobą za pomocą operatorów `and`, `or` lub `and not`. 

Frazy zawierające spacje należy ujmowć w cudzysłowy.

Przykład:
    
    name="charlie brown" and country=canada and not gender=female

#include doc/macros/pl/main_footer.bb;
