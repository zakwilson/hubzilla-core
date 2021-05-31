#Katalog główny#

Domyślnie $Projectname używa katalogów dostępnych w Internecie, które funkcjonują jako kanały.

Istnieją pewne scenariusze, w których może być potrzebny własny serwer katalogów, do którego można by podłączyć wiele portali. Ogranicza to dostęp tylko do kanałów w portalipodłączonych do tego serwera katalogowego.

##Instrukcje dotyczące konfigurowania jednego portalu jako katalogu podstawowego dla wielu portali prywatnych.##
***


* Na portalu , który będzie serwerem katalogów, otwórz plik .htconfig.php i ustaw:

    `App::$config['system']['directory_mode'] = DIRECTORY_MODE_PRIMARY;`


    Domyślnie, opcja ta powinna już być ustawiona na **DIRECTORY_MODE_NORMAL**, więc po prostu wystarczy tylko  ustawić nową wartość: **DIRECTORY_MODE_PRIMARY**

* Następnie, na każdym portalu (w tym na serwerze katalogowym), w terminalu, przejdź do folderu z kodem $Projectname i uruchom usługę katalogową:

    `util/config system directory_realm YOURREALMNAME`

    (**YOURREALMNAME** może być dowolną nazwą dziedziny katalogowej)

    po czym:

    `util/config system realm_token THEPASSWORD`
    
    (**THEPASSWORD** jest hasłem dla dziedziny katalogowej)

    **UWAGA:** Trzeba użyć tej samej nazwy dziedziny i hasła dla każdego portalu

*   Na koniec, dla każdego portalu "klienckiego", uruchom (z terminala):

    `util/config system directory_server https://theaddressofyourdirectoryserver.com`

***
Teraz, gdy przeglądasz katalog każdego portalu, powinien on pokazywać tylko kanały, które istnieją w portalach ustawionej domeny katalogowej. Do tej pory testowałem to z dwoma portalami i wydaje się, że działa dobrze.
Kanały utworzone w każdym portalu są odzwierciedlane w katalogu głównym, a następnie w katalogu wszystkich portali klienckich

##Problemy##
***

Kiedy tworzyłem pierwszy portal, był on uruchomiony i działał przez około godzinę, zanim zmieniłem go na PRIMARY_MODE, a po zmianie w katalogu nadal było kilka kanałów z całej sieci. Usunąłem je z tabeli xchan i wydaje się, że rozwiązało to problem.
