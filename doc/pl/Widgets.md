Rdzenne widżety
===============

Niektóre z tych widżetów ma ograniczenia, które mogą ograniczać typ strony, na której można umieszczać widżet lub mogą wymagać logowania


* clock - wyświetla aktualny czas
    * args: military (1 or 0) - use 24 hour time as opposed to AM/PM
<br />&nbsp;<br />

* profile - wyświetla boczny pasek profilu na stronach, które ładują profile (strony z pseudonimem w adresie URL)

* tagcloud - wyświetla tagcloud elementów strony

    * args: count - liczba elementów do jednoczesnego wyświetlenia (domyślnie 24)
<br />&nbsp;<br />

* collections - selektor grupy prywatności dla aktualnie zalogowanego kanału

    * args: mode - może to być "conversation", "group" albo "abook" w zależności od modułu
<br />&nbsp;<br />

* suggestions - sugestie znajomych dla aktualnie zalogowanego kanału

* follow - przedstawia pole tekstowe do śledzenia innego kanału

* notes - obszar prywatnych notatek dla aktualnie zalogowanego kanału, jeśli funkcja private_notes jest włączona

* savedsearch - wyszukiwanie sieci lub matrycy z zapisem - trzeba być zalogowanym i musi być włączona funkcjonalność savedsearch

* filer - wybór elementów pola ze strumienia sieci lub matrycy - musi się być zalogowanym

* archive - selektor zakresu dat dla stron sieci i kanałów
    * args: 'wall' - 1 or 0, ograniczenie do wpisów ściennych lub wpisów sieciowych/matrycowych (domyślnie)
<br />&nbsp;<br />

* fullprofile - taki sam jak obecny profil

* categories - filtr kategorii (strona kanału)

* tagcloud_wall - tagcloud tylko dla strony kanału
    * args: 'limit' - ilość tagów do wyświetlenie (domyślnie 50)
<br />&nbsp;<br />

* catcloud_wall - tagcloud dla kategorii stron kanału
    * args: 'limit' - liczba kategorii do wyświetlenia na jednej stronie (domyślnie 50)
<br />&nbsp;<br />

* affinity - suwak powinowactwa na stronie sieciowej, trzeba być zalogowanym

* settings_menu - menu paska bocznego dla strony ustawień, trzeba być zalogowanym

* mailmenu - menu paska bocznego dla strony z prywatnymi wiadomościami, trzeba sie zalogować

* design_tools - menu narzędzi projektowych do tworzenia stron internetowych, trzeba sie zalogować

* findpeople - narzędzia do wyszukiwania innych kanałów

* photo_albums - wyświetla listę albumów ze zdjęciami aktualnego właściciela strony za pomocą menu wyboru

* vcard - mini pasek boczny profilu dla osoby, którą się jest zainteresowanym (właściciel strony, cokolwiek)

* dirsafemode - narzędzie do wyboru katalogu - tylko na stronach katalogów

* dirsort - narzędzie do wyboru katalogu - tylko na stronach katalogów

* dirtags - narzędzie katalogowe - tylko na stronach katalogów

* menu_preview - wyświetlanie podgląd menu - tylko na stronach edycji menu

* chatroom_list - lista czatów dla właściciela strony

* bookmarkedchats - lista zakładek do czatów zebranych na tej stronie dla obecnego obserwatora

* suggestedchats - "ciekawe" czaty wybrane dla obecnego obserwatora

* item - wyświetla pojedynczą stronę internetową zgodnie z argumentem mid lub title
    * args:
	* channel_id - kanał, do którego należy treść, domyślnie jest to profile_uid 
	* mid - message_id strony do wyświetlenia (musi być to strona internetowa a nie element konersacji)
	* title - argument title w adresie URL strony internetowej (musi zawierać tutuł lub mid)
<br />&nbsp;<br />

* photo - wyświetla pojedyncze zdjęcie
    * args: 
    * url - adres URL zdjęcia, musu zawierać schemat http lub https
    * zrl - uwierzytelniony link zid
    * style - łańcuch stylu CSS
<br />&nbsp;<br />

* cover_photo - wyświetla zdjęcie okładkowe dla wybranego kanału
    * args:
	* channel_id - zastosowany kanał, domyślnie jest to profile_uid 
    * style - łańcuch stylu CSS (domyślnie jest dynamicznie ustawiane na szerokość regionu)
<br />&nbsp;<br />


* photo_rand - wyświetla losowe zdjęcie z jednego z albumów fotograficznych. Honorowane są uprawnienie dostępu do zdjęć
    * args: 
    * album - nazwa albumu (bardzo gorąco zalecane, jeśli ma się dużo zdjęć)
    * scale - zazwyczaj 0 (oryginalna wielkość), 1 (1024px), 2, (640px) lub 3 (320px)
    * style - łańcuch stylu CSS
	* channel_id - jeśli nie Twój
<br />&nbsp;<br />

* random_block - wyświetlić losowy element blokowy z kolekcji narzędzi do projektowania stron internetowych. Honorowane są uprawnienia dostępu.
    * args: 
    * contains - zwraca tylko bloki, które zawierają łańcuch cotains w nazwie bloku
    * channel_id - jeśłi nie Twój
<br />&nbsp;<br />

* tasklist - podać listę zadań lub spraw do załatwienia dla aktualnie zalogowanego kanału.
	* args:
	* all - jeśłi nie 0, to wyświetla ukończone zadania.
<br />&nbsp;<br />

* forums - podać listę połączonych forów publicznych z niewidocznymi liczbami dla aktualnie zalogowanego kanału.
<br />&nbsp;<br />

* activity - podać listę autorów nieprzeczytanych treści sieciowych dla aktualnie zalogowanego kanału.

* album - udostępnia widget zawierający pełny album ze zdjęciami z albumów należących do właściciela strony; może być zbyt duży, aby wyświetlić go w regionie paska bocznego, więc najlepiej jest zaimplementować to jako widżet obszaru treści. 
	* args:
	* album - nazwa albumu
	* title - opcjonalny tytuł, używana jest nazwa albumu, jeśli nie jest dostęþna
<br />&nbsp;<br />
 

Tworzenie własnych widżetów
===========================

### Widżety oparty na klasie

Aby utworzyć widżet oparty, na przykład, na klasie o nazwie "slugfish", utwórz plik o następującej zawartości:

````
<?php

namespace Zotlabs\Widget;


class Slugfish {

	function widget($args) {

	... Wstaw tutaj kod widżetu.
	... Funkcja ta zwraca łańcuch, który jest treścią HTML widżetu.
	... $args to nazwa tablicy, która przekazuje sowolne zmienne [var] z edytora układu
	... Na przykład [widget=slugfish][var=count]3[/var][/widget] wypełni $args tak:
	... [ 'count' => 3 ]

	}

````
Wynikowy plik można umieścić w widget/Slugfish/Slugfish.php lub Zotlabs/SiteWidgets/Slugfish.php. Można go również połączyć z repozytorium git za pomocą pliku util/add_widget_repo.

### Tradycyjny widget oparty na funkcjach

Jeśli chcesz mieć widżet o nazwie, na przykład, "slugfish", utwórz `widget/widget_slugfish.php` zawierający


    <?php
    
    function widget_slugfish($args) {
    
    .. wstaw tu kod widżetu. Zobacz powyższe informacje o widżetach opartych na klasie, aby uzyskać szczegółowe informacje.
    
    }


#include doc/macros/main_footer.bb;
