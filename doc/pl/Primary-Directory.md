#Katalog główny#

Domyślnie $Projectname używa katalogów dostępnych w Internecie, które funkcjonują jako kanały.

Istnieją pewne scenariusze, w których może być potrzebny własny serwer katalogów, do którego można by podłączyć wiele węzłów. Ogranicza to dostęp tylko do kanałów w węzłach podłączonych do tego serwera katalogowego.

##Instrukcje dotyczące konfigurowania jednego węzła jako katalogu podstawowego dla wielu węzłów prywatnych.##
***


* W węźle, który będzie serwerem katalogów, otwórz plik .htconfig.php i ustaw:

    `App::$config['system']['directory_mode'] = DIRECTORY_MODE_PRIMARY;`


    Domyślnie, opcja ta powinna już być ustawiona na **DIRECTORY_MODE_NORMAL**, więc po prostu wystarczy tylko  ustawić nową wartość: **DIRECTORY_MODE_PRIMARY**

* Następnie, w każdym węźle (w tym na serwerze katalogowym), w terminalu, przejdź do folderu, w którym jest zainstalowany kod węzła i uruchomić usługę katalogową:

    `util/config system directory_realm YOURREALMNAME`

    (**YOURREALMNAME** może być dowolną nazwą dziedziny katalogowej)

    po czym:

    `util/config system realm_token THEPASSWORD`
    
    (**THEPASSWORD** jest hasłem dla dziedziny katalogowej)

    **UWAGA:** Trzeba użyć tej samej nazwy dziedziny i hasła dla każdego węzła

*   Na koniec, dla każdego węzła "klienckiego", uruchom (z terminala):

    `util/config system directory_server https://theaddressofyourdirectoryserver.com`

***
Teraz, gdy przeglądasz katalog każdego węzła, powinien on pokazywać tylko kanały, które istnieją w węzłach ustawionej domeny katalogowej. Do tej pory testowałem to z dwoma węzłami i wydaje się, że działa dobrze.
Kanały utworzone w każdym węźle są odzwierciedlane w katalogu głównym, a następnie w katalogu wszystkich węzłów klienckich

##Problemy##
***

Kiedy tworzyłem pierwszy węzeł, był on uruchomiony i działał przez około godzinę, zanim zmieniłem go na PRIMARY_MODE, a po zmianie w katalogu nadal było kilka kanałów z całej matrycy. Usunąłem je z tabeli xchan i wydaje się, że rozwiązało to problem.
