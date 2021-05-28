[h1]Zaawanasowana konfiguracja dla administratorów[/h1]

[i]W tym dokumencie zakładamy, że jesteś administratorem.[/i]

$Projectname zawiera wiele opcji konfiguracyjnych, które są niedostępne w głównym panelu administracyjnym. Są to na ogół opcje uważane za zbyt niszowe, zaawansowane lub mogące być źle interpretowane przez zwykłych użytkowników.

Te ustawienia można modyfikować za pomocą powłoki, na poziomie katalogu Hubzilla najwyższego poziomu, posługując się skłądnią:

[code]util/config cat key value[/code] 
dla pojedynczej opcji konfiguracyjnej witryny, lub 

[code]util/pconfig channel_id cat key value[/code] 
dla konfiguracji kanału (członkowskiej).

W przypadku konfiguracji witryny, innym rozwiązaniem jest dodanie wiersza w pliku .htconfig.php, o składni:
[code]App::$config['cat']['key'] = 'value';[/code]

[h2]Konfiguracja kanału (pconfig)[/h2]

[dl terms="mb"]
  [*= system.always_my_theme ] Stosowanie własnego motywu podczas oglądania kanałów na tym samym portalu. Jest to realizowane w dość pomysłowy sposób, gdy przegląda się kanały w motywie zależnym od Comanche. 
  [*= system.blocked ] Blokowanie tablicy xchans przez ten kanał. Z technicznego punktu widzenia jest to ukryta konfiguracja i nie należy tutaj nic zmieniać, ale niektóre dodatki (w szczególności superblok) udostępniają ją w interfejsie użytkownika.
  [*= system.default_cipher ] Ustawienie domyślnego szyfrowania E2EE dla elementów.
  [*= system.display_friend_count ] Ustawienie liczby połączeń wyświetlanych przez widżecie połączeń profilu.
  [*= system.do_not_track ] Jako nagłówek przeglądarki. Ustawienie tego może spowodować załamanie wielu funkcji opartych na tożsamości. Naprawdę powinno się po prostu ustawić tylko takie uprawnienia, które mają sens i są niezbędne.
  [*= system.forcepublicuploads ] Wymuszenie, aby przesłane zdjęcia były publiczne, gdy są przesyłane jako elementy na ścianie. O wiele bardziej sensowne jest po prostu prawidłowe ustawienie uprawnień - zrób to zamiast ustawiać tą opcję.
  [*= system.network_page_default ] Ustawienie domyślnych parametrów dotyczących przeglądania strony internetowej. Powinno zawierać to samo zapytanie co filtrowanie ręczne.
  [*= system.paranoia ] Ustawia poziom bezpieczeństwa sprawdzania adresu IP. Jeśli adres IP zalogowanej sesji ulegnie zmianie, ten poziom zostanie wykorzystany do określenia, czy konto powinno zostać wylogowane z powodu naruszenia bezpieczeństwa. Dostępne opcje:
        0 &mdash; brak sprawdzania IP             
        1 &mdash; sprawdzenie 3 oktetów              
        2 &mdash; sprawdzenie 2 oktetów              
        3 &mdash; sprawdzenie, czy w ogóle są jakieś różnice

  [*= system.prevent_tag_hijacking ] Zapobieganie przejmowaniu przez obcej sieci hasztagów w swoich wpisach i kierowaniu ich do własnych zasobów.
  [*= system.taganyone ] Wymaganie włączenia konfiguracji o tej samej nazwie. Zezwala na tagowanie @mention każdego, niezależnie od tego, czy jest się połączony, czy nie. To się nie skaluje. 
  [*= system.anonymous_comments ] Domyślnie lub jeśli jest ustawiona na 1, umożliwia ustawienie niestandardowych uprawnień tak, aby zezwalały na anonimowe (moderowane) komentarze, takie jak WordPress, moderowane przez właściciela kanału. Jeśli jest ustawiona na 0, żaden członek tej witryny nie może tego wybrać ani włączyć. 
  [*= system.user_scalable ] Określa, czy aplikacja jest skalowalna na ekranach dotykowych. Domyślnie włączone, aby wyłączyć trzeba ustawione na zero - prawdziwe zero, a nie tylko fałsz.
[/dl]

[h2]Konfiguracja witryny[/h2]

[dl terms="mb"]
  [*= randprofile.check ] W przypadku żądania losowego profilu, najpierw sprawdza się, czy faktycznie profile takie są dostępne.
  [*= randprofile.retry ] Liczba ponownych prób pobrania losowego profilu.
  [*= system.admin_email ] Określenie adresu e-mail administratora tej witryny. Jest to wstęþnie ustawiane podczas instalacji.
  [*= system.authlog ] Plik dziennika używany do rejestrowania błędów uwierzytelniania. Służy do podłączania do oprogramowania po stronie serwera, takiego jak fail2ban. Błędy uwierzytelniania są nadal rejestrowane w dziennikach głównych.
  [*= system.auto_channel_create ] Dodanie elementów formularza niezbędnych do utworzenia pierwszego kanał na stronie rejestracji konta i utwórzemoa go (ewentualnie po sprawdzeniu przez e-mail lub po zatwierdzeniu przez administratora). Wyklucza to możliwość importowania kanału z innej witryny jako pierwszego utworzonego kanału w tej witrynie dla nowego konta. Użyj wraz z opcją system.default_permissions_role, aby usprawnić rejestrację. 
  [*= system.auto_follow ] Przy utworzeniu pierwszego kanału automatycznie obserwuj kanały tutaj wymienione - listę stron internetowych oddzielonych przecinkami (adresów w postaci członek@portal).
  [*= system.blacklisted_sites ] Portale, dla których ma być całkowicie zablokowany dostęp do tego portalu - tablica adresów URL.
  [*= system.block_public_search ] Podobne do block_public, z tą różnicą, że blokuje tylko publiczny dostęp do funkcji wyszukiwania. Przydatne w przypadku serwisów, które chcą być publiczne, ale bez możliwości publicznego przeszukiwania treści.
  [*= system.cron_hour ] Określenie godziny, w której ma być uruchamiany cron_daily. Domyślnie, bez konfiguracji Cron będzie uruchamiany o północy czasu UTC.
  [*= system.default_permissions_role ] Jeśli ustawiono prawidłową nazwę roli uprawnień, użyj tą rolę dla pierwszego kanału utworzonego przez nowe konto i nie pytaj o 'Typ kanału' w formularzu tworzenia kanału. Przykłady prawidłowych nazw to: 'social', 'social_restricted', 'social_private', 'forum', 'forum_restricted' i 'forum_private'. Więcej informacji znadziesz [tutaj](/help/roles).
  [*= system.default_profile_photo ] Ustawienie zdjęcia profilowego, dla nowych kanałów. Powinna być to nazwa katalogu znajdującego się w images/default_profile_photos/ lub lub być pustą wartością. Jeśli opcja nie jest ustawione, zakłada się, że stosowany będzie obrazek 'rainbow_man'.
  [*= system.directorytags ] Ustawienie liczby tagów słów kluczowych wyświetlanych na stronie katalogu. Wartość domyślna to 50.
  [*= system.disable_directory_keywords ] Jeśli '1', nie pokazuj słów kluczowych z katalogu. Jeśli portal jest serwerem katalogowym, nie zezwalaj na zwracanie znaczników do żadnych klientów katalogu. Nie ustawiaj tego dla serwerów katalogowych w dziedzinie RED_GLOBAL. 
  [*= system.disable_discover_tab ] Pozwala to całkowicie wyłączyć możliwość wykrywania treści publicznych z witryn zewnętrznych.
  [*= system.disable_dreport ] Jeśli '1', nie przechowuj raportów doręczenia ani nie stosuj do nich odnośników.
  [*= system.dlogfile ] Plik dziennika używany do rejestrowania błędów programistycznych. Dokładnie to samo, co rejestrator w innym przypadku. To nie jest magia i wymaga własnych instrukcji logowania. Narzędzie programistyczne.
  [*= system.email_notify_icon_url ] URL obrazu (32x32) do wyświetlenia w powiadomieniach e-mail (treści HTML).
  [*= system.expire_delivery_reports ] Ważność raportów doręczeń w dniach - domyślnie 10.
  [*= system.expire_limit ] Nie wygaszaj więcej niż ta liczba wpisów w kanale w ramach jednego uruchomienia wygaszania, aby nie wyczerpać pamięci. Domyślnie 5000.
  [*= system.photo_storage_type] Jeśli '1', użyj systemu plików, zamiast bazy danych SQL, do przechowywania miniatur. Wartość domyślna to '0'. Wprowadzono w wersji 4.2.
  [*= system.hidden_version_siteinfo ] Jeśli true, nie wyświetlaj wersji oprogramowania na stronach informacji o witrynie (system.hide_version również ukrywa wersję na tych stronach, ale to ustawienie *tylko* ukrywa wersję na stronach informacji o witrynie).
  [*= system.hide_help ] Nie wyświetlaj linku do stron pomocy na pasku nawigacyjnym.
  [*= system.hide_in_statistics ] Poinformuj serwery statystyk, aby całkowicie ukryły ten portal na liście portali.
  [*= system.hide_version ] Jeśli true, nie zgłaszaj wersji oprogramowania na stronach internetowych ani w narzędziach. (*) Trzeba to ustawić w .htconfig.php.
  [*= system.ignore_imagick ] Zignoruj imagick i używaj GD, nawet jeśli imagick jest zainstalowany na serwerze. Zapobiega to niektórym problemom z plikami PNG w starszych wersjach programu imagick.
  [*= system.max_daily_registrations ] Ustaw maksymalną liczbę nowych rejestracji dozwolonych w jednym dniu. Przydatne, aby zapobiec nadmiernej subskrypcji po nagłym nagłośnieniu projektu.
  [*= system.max_import_size ] Jeśli skonfigurowano, jest to maksymalna wielkość importowanej wiadomości tekstowej. Zwykle jest to 200 KB lub więcej, aby pomieścić prywatne zdjęcia Friendica, które są osadzane w wiadomości.
  [*= system.max_tagged_forums ] Zapobieganie spamowi. Ogranicza liczbę tagowanych forów, które są rozpoznawane w każdym wpisie. Wartość domyślna to 2. Tylko pierwsze n tagów zostanie dostarczone jako forum, pozostałe nie spowodują dostarczenia. 
  [*= system.minimum_feedcheck_minutes ] Minimalny odstęp czasu między odpytywaniem źródeł kanałów RSS. Jeśli jest mniejszy niż interwał Cron, źródła danych będą odpytywane przy każdym uruchomieniu zadań Crona. Wartość domyślna to 60, jeśli nie jest ustawiona. Ustawienie witryny można również nadpisać dla każdego kanału za pomocą ustawienia klasy usług o nazwie 'minimum_feedcheck_minutes'.
  [*= system.no_age_restriction ] Nie ograniczaj rejestracji do osób w wieku powyżej 13 lat. W wielu krajach pociąga to za sobą prawne obowiązki wymagające podania wieku i blokowania wszystkich danych osobowych nieletnich, dlatego przed zmianą należy sprawdzić lokalne przepisy.  
  [*= system.object_cache_days] Ustaw, jak długo ma być buforowana zawartość osadzona, bez ponownego pobierania. Wartość domyślna to 30 dni. 
  [*= system.openssl_conf_file ] Określa plik zawierający konfigurację OpenSSL. Wymagane w niektórych instalacjach systemu Windows do zlokalizowania w systemie pliku konfiguracyjnego openssl. Najpierw przeczytaj kod. Jeśli nie potrafisz odczytać kodu, nie baw się tym.
  [*= system.openssl_encrypt ] Użyj mechanizmu szyfrowania openssl, wartość domyślna to false (używa wtedy mcrypt do szyfrowania AES).
  [*= system.optimize_items ] Uruchamia optimise_table podczas niektórych zadań, aby baza danych była prawidłowa i nie zdefragmentowana. Jest to związane ze spadkiem wydajności podczas uruchamiania, ale w reaultacie powoduje, że rzeczy są nieco szybsze. Istnieją również narzędzia CLI do wykonywania tej operacji, które warto preferować, zwłaszcza w dużych serwisach.
  [*= system.override_poll_lockfile ] Zignorowanie pliku blokady w procesie odpytywania, aby umożliwić jednoczesne działanie więcej niż jednego procesu.
  [*= system.paranoia ] Tak samo jak pconfig, ale na poziomie całej witryny. Ustawienie to może być nadpisane przez ustawienia członkowskie.
  [*= system.pin_types ] Tablica dozwolonych typów elementów, które mozna przypiąć. Wartości domyślne zależą od modułu, ale można je tutaj ponownie zdefiniować.
  [*= system.photo_cache_time ] Jak długo buforować zdjęcia w sekundach. Wartość domyślna to 86400 (1 dzień). Dłuższy czas zwiększa wydajność, ale oznacza również, że zastosowanie zmienionych uprawnień trwa dłużej.
  [*= system.platform_name ] Co zgłosić jako nazwę platformy na stronach internetowych i w statystykach. (*) Musi być ustawione w .htconfig.php
  [*= system.rating_enabled ] Rozproszone raportowanie reputacji i gromadzenie danych. Ta funkcja jest obecnie poprawiana.
  [*= system.poke_basic ] Zmniejsz liczbę wyrażeń szturchnięć ("poke verbs") do dokładnie 1 ("poke"). Wyłącz inne wyrażenia. 
  [*= system.proc_run_use_exec ] Jeśli 1, użycie wywołania systemowego exec w proc_run do uruchomienia zadania w tle. Domyślnie używamy proc_open i proc_close. W niektórych (obecnie rzadkich) systemach nie działa to dobrze.
  [*= system.projecthome ] Wyświetl stronę projektu na swojej stronie głównej dla wylogowanych osób.
  [*= system.projecthome ] Ustaw stronę główną projektu jako stronę główną swojego portalu. (Przestarzałe)
  [*= system.register_link ] Ścieżka do strony z linku "Zarejestruj się" w formularzu logowania. W zamkniętych witrynach będzie to przekierowywać do 'pubsites'. W przypadku otwartych witryn zwykle przekierowuje do strony 'register', ale można to zmienić na niestandardową stronę oferującą subskrypcje lub cokolwiek innego. 
  [*= system.reserved_channels ] Nie zezwalaj członkom na rejestrowanie kanałów o nazwach wyszczególnionych na tej liście nazw rozddzielanych przecinkami (bez spacji).
  [*= system.sellpage ] Adres URL wyświetlany na liście witryn publicznych, prowadzący do strony z cenami usług - parametry kont i ich cena itp.
  [*= system.startpage ] Ustawienie domyślnej strony, która ma być otwierana po zalogowaniu do jakiegokolwiek kanału w tym serwisie. Ustawienie to może zostać nadpisane przez ustawienia użytkownika.
  [*= system.sys_expire_days ] Ile dni należy zachować odkryte treści publiczne z innych serwisów.
  [*= system.taganyone ] Zezwolenie na oznaczanie tagiem @mention każdego, niezależnie od tego, czy jest się połączonym, czy nie.
  [*= system.tempdir ] Miejsce przechowywania plików tymczasowych (aktualnie nieużywane), domyślnie jest zdefiniowane w konfiguracji PHP.
  [*= system.tos_url ] Ustawienie alternatywnego linku do lokalizacji ToS.
  [*= system.transport_security_header ] Jeśli jest to wartość niezerowa i włączona jest obsługa SSL, stronach serwisu dołączany bedzie nagłówek HTTP strict-transport-security.
  [*= system.uploaddir ] Lokalizacja katalogu przesyłania plików (domyślnie jest to system.tempdir, obecnie używany tylko przez wtyczkę js_upload).
  [*= system.workflow_channel_next ] Strona do której sa kierowani wszyscy nowi członkowie bezpośrednio po utworzeniu kanału.
  [*= system.workflow_register_next ] Strona do któtrej kierowani są członkowie bezpośrednio po utworzeniu konta (tylko wtedy, gdy włączona jest opcja auto_channel_create lub UNO).
[/dl]


[h3]Konfiguracja katalogu[/h3]

[h4]Domyślne wartości przeszukiwania katalogu[/h4]

[dl terms="mb"]
  [*= directory.globaldir ] 0 lub 1. Domyślnie 0, jeśli odwiedza się katalog w witrynie, domyślnie zobaczy się tylko członków tej witryny. Trzeba przejść dodatkowy krok, aby zobaczyć osoby w pozostałej części sieci; Po zrobieniu tego, wyraźnie widać, że te osoby nie  są członkami tego serwisu, ale należą do większej sieci.
  [*= directory.pubforums ] 0 lub 1. Domyślnie 0 - publiczne fora.
  [*= directory.safemode ] 0 lub 1.  
[/dl]

[h4]Konfiguracja serwera katalogowego[/h4][i](see [zrl=[baseurl]/help/directories]help/directories[/zrl])[/i]

[dl terms="mb"]
  [*= system.directory_mode ]
  [*= system.directory_primary ]
  [*= system.directory_realm ]
  [*= system.directory_server ]
  [*= system.realm_token ]
[/dl]

#include doc/macros/main_footer.bb;

