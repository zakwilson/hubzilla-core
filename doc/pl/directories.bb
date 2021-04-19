[h3]Konfiguracja katalogu[/h3]

Katalogi w $Projectname służą do wyszukiwania i lokalizowania członków w całej sieci Zot. Są również używane do przechowywania i wysyłania zapytań o "oceny" członków i serwisów. Katalogi są dystrybuowane i dublowane, więc awaria jednej z nich nie spowoduje wyłączenia lub zakłócenia całej sieci.

[b]Standardowa konfiguracja[/b]

Nowe serwisy działają zaraz po uruchomieniu jako klienty katalogi będą, podczas pierwszego uruchomienia, automatycznie wybierać serwery katalogowe z zakodowanej listy. Możesz przeanalizować lub zmienić tą cechę za pomocą polecenia: 

[code]
util/config system directory_server
[/code]

Aby ustawić inny serwer katalogowy:

[code]
util/config system directory_server https://newdirectory.something
[/code]

[b]Konfiguracja autonomicznego sewera[/b]

Niektóre serwisy mogą chcieć działać w trybie "autonomicznym" i nie łączyć się z żądnymi zewnętrznymi serwerami katalogami. Jest to przydatne w przypadku serwisów izolowanych ("poza siecią") i serwisów testowych, ale może być również przydatne dla małych organizacji, które nie chcą łączyć się z innymi serwisami w sieci. 

Aby to skonfigurować, poszukaj w swoim pliku .htconfig.php następującego tekstu i odpowiednio ustaw konfigurację.

[code]
// Configure how we communicate with directory servers.
// DIRECTORY_MODE_NORMAL     = directory client, we will find a directory
// DIRECTORY_MODE_SECONDARY  = caching directory or mirror
// DIRECTORY_MODE_PRIMARY    = main directory server
// DIRECTORY_MODE_STANDALONE = "off the grid" or private directory services

App::$config['system']['directory_mode']  = DIRECTORY_MODE_STANDALONE;
[/code]


[b]Konfiguracja serwera pomocniczego[/b]

Można także skonfigurować swoją witrynę jako serwer pomocniczy. Działa to jako kopia lustrzana katalogu głównego i umożliwia podział obciążenia między dostępne serwery. Istnieje bardzo niewielka różnica funkcjonalna między podstawowym i pomocniczym serwerem, jednak może istnieć tylko jeden główny serwer katalogowy na dziedzinę (dziedziny są omówione w dalszej części tego dokumentu).

Przed zdecydowaniem się na obsługę serwera katalogowego należy pamiętać, że trzeba być aktywnym członkiem sieci oraz mieć zasoby i czas do zarządzania tymi usługami. Zwykle nie wymaga to specjalnego zarządzania, ale niezbędna jest większa stabilność, ponieważ utrata serwera katalogów może powodować problemy z klientami katalogów, którzy są od niego zależni.


[b]Konfiguracja serwera katalogowego[/b]

Jeśli serwer katalogowy wskazuje, że nie jest już serwerem katalogów, powinno to zostać wykryte przez oprogramowanie i konfiguracja tego serwera zostanie usunięta (wygaszona). Jeśli przejdzie w tryb offline na stałe bez ostrzeżenia, będziesz wiedzieć tylko wtedy, gdy członkowie witryny zgłoszą, że katalogi są niedostępne. Obecnie administrator witryny może to naprawić tylko ręcznie, wybierając nowy katalog i wykonując polecenie:

[code]
util/config system directory_server https://newdirectory.something
[/code]

Mamy nadzieję, że w przyszłości będzie to konfigurowane w polu do wyboru w panelu administracyjnego serwisu. 


[h2]Dziedziny katalogów[/h2]

Duże organizacje mogą chcieć używać "dziedzin" katalogów zamiast pojedynczego samodzielnego katalogu. Dziedzina standardowa i domyślna jest znana jako RED_GLOBAL. Tworząc nową dziedzinę, organizacja ma możliwość tworzenia własnej hierarchii serwerów głównych i pomocniczych oraz klientów. 

[code]
util/config system directory_realm MY_REALM
[/code]

Twoja dziedzina musi mieć katalog główny. Utwórz to najpierw. Następnie ustaw tę samą dziedzinę we wszystkich serwisach w dziedzinie katalogu (serwerach i klientach). 

Możesz także podać "dziedzinę podrzędną", która działa niezależnie od dziedziny RED_GLOBAL (lub dowolnej innej dziedziny), ale umożliwia wzajemne członkostwo i pewne możliwości wyszukiwania członków w całej przestrzeni katalogu. To przeszło tylko lekkie testy, więc przygotuj się na pomoc i naprawienie wszelkich problemów, które mogą się pojawić. Dziedzina podrzędna zawiera dziedzinę nadrzędną w nazwie dziedziny.

 
[code]
util/config system directory_realm RED_GLOBAL:MY_REALM
[/code]
  

[b]Dostęp do dziedziny[/b]

Można zrobić tak, aby Twoje serwery katalogowe i usługi były używane tylko przez członków Twojej dziedziny. W tym celu trzeba zastosować podawanie tokenu lub hasła, aby uzyskać dostęp do usług katalogowych dziedziny. Ten token nie jest szyfrowany podczas przesyłania, ale jest wystarczający, aby zapobiec przypadkowemu dostępowi do serwerów katalogowych. Dla wszystkich lokacji (klientów i serwerów katalogowych) w dziedzinie należy skonfigurować następujące elementy:

[code]
util/config system realm_token my-secret-realm-password
[/code]


[h2]Katalogi lustrzane[/h2]

Dublowanie odbywa się przy uzyciu dziennika transakcji, które są współużytkowane przez serwery katalogowe. W przypadku aktualizacji katalogu i profilu przesyłany jest adres kanału przeprowadzającego aktualizację, a inne serwery katalogów sondują ten kanał u źródła pod kątem zmian. Nie ufamy i nie powinniśmy ufać żadnym informacjom przekazanym nam przez inne serwery katalogowe. Informacje zawsze trzeba sprawdzać u źródła.  

Oceny są obsługiwane nieco inaczej - zaszyfrowany pakiet (podpisany przez kanał, który utworzył ocenę) jest przesyłany między serwerami. Ten podpis musi zostać zweryfikowany przed zaakceptowaniem oceny. Oceny są zawsze publikowane na głównym serwerze katalogów i stamtąd propagowane do wszystkich innych serwerów katalogów. Z tego powodu w dziedzinie może istnieć tylko jeden serwer główny. Jeśli źle skonfigurowana witryna twierdzi, że jest katalogiem głównym, jest ignorowana w dziedzinie RED_GLOBAL. W innych dzidzinach obecnie nie ma takiej ochrony. Należy o tym pamiętać podczas pracy z alternatywnymi dziedzinami.

Nowo utworzone serwery katalogowe nie otrzymują "pełnego zrzutu", ale ze względu na wydajność i minimalne zakłócenia działania innych serwerów w sieci, są one powoli przełączane do trybu online. Może minąć nawet miesiąc, zanim nowy pomocniczy serwer katalogowy zapewni pełny widok sieci. Nie dodawaj żadnych serwerów pomocniczych do zakodowanej listy rezerwowych serwerów katalogowych, dopóki nie będą one działać jako katalog przez co najmniej miesiąc.

Wszystkie kanały są skonfigurowane do "pingowania" swojego serwera katalogowego raz w miesiącu, w nieco losowych momentach w ciągu miesiąca. Daje to katalogowi możliwość wykrywania martwych kanałów i witryn (przestają one pingować). Następnie są oznaczane jako martwe lub nieosiągalne i z czasem będą usuwane z wyników katalogu.

Kanały można skonfigurować tak, aby były "ukryte" w katalogu. Te kanały mogą nadal istnieć w katalogu, ale nie będzie można ich przeszukiwać, a niektóre "wrażliwe" informacje osobiste nie będą w ogóle przechowywane.

       