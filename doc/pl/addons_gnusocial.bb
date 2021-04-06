[b]Jak przesyłać wpisy do instancji GNUsocial[/b]

Zacznij od instancji GNUSocial, w której masz swoje konto.

W instancji GNUSocial przejdź do Ustawienia > Połączenia. W prawej kolumnie, w sekcji "Programiści", kliknij link "Zarejestruj aplikację kliencką OAuth, która ma być używana z tym wystąpieniem StatusNet". Ten link można znaleźć w Twojej instancji tutaj:

https://yourgnusocialinstance.org/settings/oauthapps

Następnie kliknij łącze "Zarejestruj nową aplikację". Pojawi się nowy formularz zgłoszeniowy. Oto, co należy zrobić na każdym polu.

Ikona. Pobierz ikonę $Projectname znajdującą się pod tym linkiem, po zapisaniu jej na swoim komputerze:

https://framagit.org/hubzilla/core/blob/master/images/rm-32.png

Nazwa. Nadaj aplikacji odpowiednią nazwę. Wywołaj swoją witrynę Hubzilli. Możesz preferować r2g.

Opis. Użyj tego pola, aby opisać przeznaczenie aplikacji. Dodaj coś o efekcie użycia krzyżowego wysyłania z $Projectname do GNUsocial.

Źródłowy adres URL. Wpisz nazwę domeny głównej witryny Red, której używasz. Nie zapomnij wpisać "s" w https://yourhubzillasite.com. Jeśli Twoja instalacja Red jest subdomeną, prawdopodobnie będzie to wymagane.

Organizacja. Jeśli używasz tej instancji $Projectname dla grupy lub firmy, wypełnij to pole.

Strona główna. Jeśli Twoja grupa korzysta z subdomeny, prawdopodobnie zechcesz umieścić tutaj identyfikator URI domeny głównej.

Adres URL wywołania zwrotnego. Pozostaw puste.

Typ aplikacji: wybierz "desktop."

Domyślny dostęp: wybierz "Read-write."

Wszystkie pola oprócz adresu URL wywołania zwrotnego muszą być wypełnione.

Kliknij przycisk "Zapisz".

Następnie kliknij ikonę lub nazwę aplikacji, aby wyświetlić informacje, które musisz wstawić w $Projectname.

*****

Otwórz teraz nową kartę lub okno i przejdź do swojego konta $Projectname, do ustawień Ustawienia > Właściwości. Znajdź ustawienia publikowania StatusNet.

Wstaw w $Projectname ciągi liczb, podane na stronie GNUsocial, do pól klucza konsumenta i hasła konsumenta.

Podstawową ścieżką API (pamiętaj o końcowym znaku /) będzie adres Twojej domeny i ścieżki "/api/". Prawdopodobnie będzie wyglądać tak:

https://yourgnusocialinstance.org/api/

W przypadku wątpliwości sprawdź witrynę instancji GNUsocial, aby znaleźć adresy URL domeny tokenu żądania, tokenu dostępu i autoryzacji. Będzie to pierwsza część adresu URL domene, bez "/oauth/...."

Nazwa aplikacji StatusNet: Wstaw nazwę, którą nadałeś aplikacji w witrynie GNUsocial.

Kliknij "Prześlij".

Pojawi się przycisk "Zaloguj się do StatusNet". Kliknij go, a otworzy się zakładka lub okno w witrynie GNUsocial, w którym możesz kliknąć "Zezwól". Po kliknięciu i pomyślnej autoryzacji pojawi się numer kodu bezpieczeństwa. Skopiuj go i wróć do aplikacji $Projectname, którą właśnie opuściłeś i wstaw ją w polu: "Tutaj skopiuj kod bezpieczeństwa ze StatusNet". Kliknij "Prześlij".

Jeśli się powiedzie, Twoje informacje z instancji GNUsocial powinny pojawić się w aplikacji $Projectname.

Jeśli chcesz, masz teraz do wyboru kilka opcji, które należy również potwierdzić, klikając "Prześlij". Najbardziej interesująca jest opcja "Domyślnie wysyłaj publiczne wpisy do StatusNet". Ta opcja automatycznie wysyła wszystkie wpisy, które napisałeś na koncie $Projectname do Twojej instancji GNUsocial.

Jeśli nie wybierzesz tej opcji, będziesz mieć możliwość ręcznego wysłania wpisu do swojej instancji GNUsocial. W tym celu, najpierw otwórz wpis (klikając w obszarze tekstowym wpisu) i kliknik ikonę kłódki obok przycisku "Udostępnij". Wybierz ikonę GNUsocial składającą się z trzech kolorowych dymków dialogowych. Zamknij to okno, a następnie wykonaj swój wpis.

Jeśli wszystko pójdzie dobrze, właśnie wysłałeś swój wpis z $Projectname na swoje konto w instancji GNUsocial.

#include doc/macros/addons_footer.bb;

